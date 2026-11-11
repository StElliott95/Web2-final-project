@extends('layouts.app')

@section('title', $movie->title . ' Discussion')

@section('app-content')
    <section class="discussion-room" id="discussion-room"
        data-stream-url="{{ route('integrations.discussion-stream', ['room' => $room->id, 'after' => $messages->last()?->id ?? 0]) }}">
        <a class="discussion-back" href="{{ route('movies.show', $movie->tmdb_id) }}">← Back to movie</a>
        <p class="eyebrow">MOVIE DISCUSSION</p>
        <h1>{{ $room->title ?: $movie->title . ' discussion' }}</h1>
        <p class="discussion-status" id="connection-status" role="status">Connecting to live discussion…</p>

        <div class="discussion-messages" id="discussion-messages" aria-live="polite" aria-relevant="additions">
            @forelse($messages as $message)
                <article class="discussion-message" data-message-id="{{ $message->id }}">
                    <header>
                        <strong>{{ $message->sender->name }}</strong>
                        <time datetime="{{ $message->created_at?->toIso8601String() }}">{{ $message->created_at?->format('H:i') }}</time>
                    </header>
                    <p>{{ $message->message }}</p>
                </article>
            @empty
                <p class="discussion-empty">No messages yet. Start the conversation.</p>
            @endforelse
        </div>

        <form class="discussion-composer" id="discussion-form"
            action="{{ route('integrations.discussion-messages', $room->id) }}" method="POST">
            @csrf
            <label for="discussion-message">Message</label>
            <textarea id="discussion-message" name="message" maxlength="1000" rows="3" required></textarea>
            <button type="submit">Send message</button>
            <p id="send-status" role="status"></p>
        </form>
    </section>

    <style>
        .discussion-room { display: grid; gap: 14px; max-width: 850px; margin: auto; }
        .discussion-room h1, .discussion-room p { margin-top: 0; }
        .discussion-back { color: #e0ae55; text-decoration: none; }
        .discussion-status { color: #a8b0bb; }
        .discussion-messages { display: grid; gap: 10px; max-height: 60vh; min-height: 260px; overflow-y: auto; padding: 14px; border: 1px solid #2d3541; border-radius: 10px; background: #151b22; }
        .discussion-message { padding: 12px; border-radius: 8px; background: #202832; }
        .discussion-message header { display: flex; justify-content: space-between; gap: 12px; color: #e0ae55; }
        .discussion-message time { color: #a8b0bb; font-size: 13px; }
        .discussion-message p { margin: 8px 0 0; white-space: pre-wrap; overflow-wrap: anywhere; }
        .discussion-empty { color: #a8b0bb; }
        .discussion-composer { display: grid; gap: 10px; }
        .discussion-composer button { justify-self: start; }
    </style>

    <script>
    (() => {
        const root = document.getElementById('discussion-room');
        const list = document.getElementById('discussion-messages');
        const form = document.getElementById('discussion-form');
        const status = document.getElementById('connection-status');
        const sendStatus = document.getElementById('send-status');

        function appendMessage(message) {
            const selector = '[data-message-id="' + Number(message.id) + '"]';
            if (list.querySelector(selector)) return;
            const empty = list.querySelector('.discussion-empty');
            if (empty) empty.remove();

            const article = document.createElement('article');
            article.className = 'discussion-message';
            article.dataset.messageId = String(message.id);

            const header = document.createElement('header');
            const sender = document.createElement('strong');
            sender.textContent = message.sender || 'User';
            const time = document.createElement('time');
            time.textContent = message.sent_at ? new Date(message.sent_at).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }) : '';
            header.append(sender, time);

            const text = document.createElement('p');
            text.textContent = message.message || '';
            article.append(header, text);
            list.appendChild(article);
            list.scrollTop = list.scrollHeight;
        }

        const stream = new EventSource(root.dataset.streamUrl);
        stream.addEventListener('open', () => { status.textContent = 'Connected. Messages update live.'; });
        stream.addEventListener('message', event => {
            try { appendMessage(JSON.parse(event.data)); } catch (_) {}
        });
        stream.addEventListener('error', () => { status.textContent = 'Connection interrupted; reconnecting…'; });

        form.addEventListener('submit', async event => {
            event.preventDefault();
            sendStatus.textContent = '';
            const button = form.querySelector('button[type="submit"]');
            const textarea = form.querySelector('textarea[name="message"]');
            button.disabled = true;
            try {
                const response = await fetch(form.action, {
                    method: 'POST',
                    body: new FormData(form),
                    headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
                });
                const data = await response.json();
                if (!response.ok) throw new Error(data.message || (data.errors && data.errors.message ? data.errors.message[0] : 'Message could not be sent.'));
                appendMessage(data);
                textarea.value = '';
                textarea.focus();
            } catch (error) {
                sendStatus.textContent = error.message || 'Message could not be sent.';
            } finally {
                button.disabled = false;
            }
        });

        list.scrollTop = list.scrollHeight;
        window.addEventListener('pagehide', () => stream.close(), { once: true });
    })();
    </script>
@endsection
