@extends('layouts.app')

@section('title', $title)

@section('app-content')
<h1>{{ $title }}</h1>
<p><a href="{{ route('list-users') }}">← Back to people</a></p>
<div class="people-list">

    @forelse($data as $row)
<div class="person-row">
<span class="person-avatar" aria-hidden="true">{{ strtoupper(mb_substr($row->name, 0, 1)) }}</span>
<div class="person-info">
<strong><a href="{{ route('user-profile', $row->id) }}">{{ $row->name }}</a></strong>
<small>Since {{ $row->pivot->created_at->format('d M Y') }}</small>
</div>

            @if($row->id != auth()->id())

                @if(in_array($row->id, $myFollowing))
<form action="{{ route('unfollow-user', $row->id) }}" method="POST">

                        @csrf

                        @method('DELETE')
<button type="submit" class="secondary">Unfollow</button>
</form>

                @else
<form action="{{ route('follow-user', $row->id) }}" method="POST">

                        @csrf
<button type="submit">Follow</button>
</form>

                @endif

            @endif
</div>

    @empty
<p class="people-empty">Nobody here yet.</p>

    @endforelse
</div>
<style>

    .people-list { max-width: 720px; display: grid; gap: 12px; margin-top: 16px; }

    .person-row { display: flex; align-items: center; gap: 12px; background: var(--surface); border: 1px solid var(--border); border-radius: 8px; padding: 12px 16px; }

    .person-row form { margin: 0; }

    .person-avatar { width: 40px; height: 40px; border-radius: 50%; background: var(--surface-raised); color: var(--accent); display: grid; place-items: center; font-weight: 700; flex: 0 0 auto; }

    .person-info { flex: 1; min-width: 0; display: flex; flex-direction: column; }

    .person-info strong { overflow-wrap: anywhere; }

    .person-info small { color: var(--muted); }

    .person-row button.secondary { background: transparent; color: var(--text); border: 1px solid var(--border); }

    .people-empty { color: var(--muted); }
</style>

@endsection
 