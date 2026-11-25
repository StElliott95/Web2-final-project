@extends('layouts.app')

@section('title', 'People')

@section('app-content')
<h1>Find people</h1>
<form method="GET" action="{{ route('list-users') }}" class="people-search">
<label for="search" class="sr-only">Search people</label>
<input type="text" id="search" name="search" value="{{ request('search') }}" placeholder="Search by name or email">
<button type="submit">Search</button>
</form>
<div class="people-list">

    @forelse($data as $row)
<div class="person-row">
<span class="person-avatar" aria-hidden="true">{{ strtoupper(mb_substr($row->name, 0, 1)) }}</span>
<div class="person-info">
<strong><a href="{{ route('user-profile', $row->id) }}">{{ $row->name }}</a></strong>
<small>{{ $row->get_followers_count }} {{ \Illuminate\Support\Str::plural('follower', $row->get_followers_count) }}</small>
</div>

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
</div>

    @empty
<p class="people-empty">No users found.</p>

    @endforelse
</div>
<style>

    .people-search { display: flex; gap: 8px; max-width: 560px; margin: 0 0 24px; }

    .people-search input { flex: 1; }

    .people-list { max-width: 720px; display: grid; gap: 12px; }

    .person-row { display: flex; align-items: center; gap: 12px; background: var(--surface); border: 1px solid var(--border); border-radius: 8px; padding: 12px 16px; }

    .person-avatar { width: 40px; height: 40px; border-radius: 50%; background: var(--surface-raised); color: var(--accent); display: grid; place-items: center; font-weight: 700; flex: 0 0 auto; }

    .person-info { flex: 1; min-width: 0; display: flex; flex-direction: column; }

    .person-info strong { overflow-wrap: anywhere; }

    .person-info small { color: var(--muted); }

    .person-row button.secondary { background: transparent; color: var(--text); border: 1px solid var(--border); }

    .people-empty { color: var(--muted); }

    .sr-only { position: absolute; width: 1px; height: 1px; overflow: hidden; clip: rect(0 0 0 0); }
</style>

@endsection
 