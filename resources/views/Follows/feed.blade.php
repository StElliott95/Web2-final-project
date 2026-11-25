@extends('layouts.app')

@section('title', 'Feed')

@section('app-content')
<h1>Activity of people you follow</h1>
<div class="feed-list">

    @forelse($activities as $item)
<div class="feed-row">
<span class="feed-avatar" aria-hidden="true">{{ strtoupper(mb_substr($item['user']->name, 0, 1)) }}</span>
<div class="feed-info">
<p>
<a href="{{ route('user-profile', $item['user']->id) }}"><strong>{{ $item['user']->name }}</strong></a>

                    {{ $item['text'] }}

                    @if($item['link'])

                        · <a href="{{ $item['link'] }}">open</a>

                    @endif
</p>
<small>{{ $item['date']->diffForHumans() }}</small>
</div>
</div>

    @empty
<p class="feed-empty">Nothing here yet. <a href="{{ route('list-users') }}">Find people to follow</a>.</p>

    @endforelse
</div>
<style>

    .feed-list { max-width: 720px; display: grid; gap: 12px; margin-top: 16px; }

    .feed-row { display: flex; align-items: flex-start; gap: 12px; background: var(--surface); border: 1px solid var(--border); border-radius: 8px; padding: 12px 16px; }

    .feed-avatar { width: 40px; height: 40px; border-radius: 50%; background: var(--surface-raised); color: var(--accent); display: grid; place-items: center; font-weight: 700; flex: 0 0 auto; }

    .feed-info { flex: 1; min-width: 0; }

    .feed-info p { margin: 0; overflow-wrap: anywhere; }

    .feed-info small { color: var(--muted); }

    .feed-empty { color: var(--muted); }
</style>

@endsection
 