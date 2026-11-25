@extends('layouts.app')
@section('title', $user->name)
@section('app-content')
<header class="profile-head">
<span class="profile-avatar" aria-hidden="true">{{ strtoupper(mb_substr($user->name, 0, 1)) }}</span>
<div class="profile-info">
<h1>{{ $user->name }}</h1>
       @if(filled($user->bio))
<p class="profile-bio">{{ $user->bio }}</p>
       @endif
<p class="profile-stats">
<a href="{{ route('user-followers', $user->id) }}"><strong>{{ $user->get_followers_count }}</strong> {{ \Illuminate\Support\Str::plural('follower', $user->get_followers_count) }}</a>
<a href="{{ route('user-following', $user->id) }}"><strong>{{ $user->get_following_count }}</strong> following</a>
</p>
</div>
   @if(! $isMe)
       @if($isFollowing)
<form action="{{ route('unfollow-user', $user->id) }}" method="POST">
               @csrf
               @method('DELETE')
<button type="submit" class="secondary">Unfollow</button>
</form>
       @else
<form action="{{ route('follow-user', $user->id) }}" method="POST">
               @csrf
<button type="submit">Follow</button>
</form>
       @endif
   @endif
</header>
<div class="profile-grid">
<section class="profile-card" aria-labelledby="profile-reviews">
<h2 id="profile-reviews">Reviews</h2>
       @if(! $isMe && ! $user->show_reviews)
<p class="profile-muted">{{ $user->name }} keeps reviews private.</p>
       @else
           @forelse($reviews as $row)
<div class="profile-item">
<div class="profile-item-head">
<a href="{{ route('list-reviews', $row->movie_id) }}">{{ $row->getMovie->title }}</a>
<span class="profile-rating">★ {{ $row->rating }}/10</span>
</div>
                   @if(filled($row->body))
<p>{{ \Illuminate\Support\Str::limit($row->body, 160) }}</p>
                   @endif
</div>
           @empty
<p class="profile-muted">No reviews yet.</p>
           @endforelse
       @endif
</section>
<section class="profile-card" aria-labelledby="profile-lists">
<h2 id="profile-lists">Public lists</h2>
       @if(! $isMe && ! $user->show_lists)
<p class="profile-muted">{{ $user->name }} keeps lists private.</p>
       @else
           @forelse($lists as $list)
<div class="profile-item">
<div class="profile-item-head">
<a href="{{ route('lists.show', $list->id) }}">{{ $list->title }}</a>
<span class="profile-muted">{{ $list->movies_count }} {{ \Illuminate\Support\Str::plural('movie', $list->movies_count) }}</span>
</div>
</div>
           @empty
<p class="profile-muted">No public lists yet.</p>
           @endforelse
       @endif
</section>
<section class="profile-card" aria-labelledby="profile-watched">
<h2 id="profile-watched">Recently watched</h2>
       @if(! $isMe && ! $user->show_watched)
<p class="profile-muted">{{ $user->name }} keeps watch history private.</p>
       @else
           @forelse($watched as $row)
<div class="profile-item">
<div class="profile-item-head">
<span>{{ $row->getMovie->title }}</span>
<span class="profile-muted">{{ $row->watched_at->diffForHumans() }}</span>
</div>
</div>
           @empty
<p class="profile-muted">Nothing watched yet.</p>
           @endforelse
       @endif
</section>
</div>
<style>
   .profile-head { display: flex; align-items: center; gap: 16px; margin-bottom: 24px; flex-wrap: wrap; }
   .profile-head form { margin: 0; }
   .profile-avatar { width: 64px; height: 64px; border-radius: 50%; background: var(--surface-raised); color: var(--accent); display: grid; place-items: center; font-size: 26px; font-weight: 700; flex: 0 0 auto; }
   .profile-info { flex: 1; min-width: 0; }
   .profile-info h1 { margin: 0; overflow-wrap: anywhere; }
   .profile-bio { margin: 4px 0 0; color: var(--muted); }
   .profile-stats { display: flex; gap: 16px; margin: 6px 0 0; }
   .profile-stats a { color: var(--muted); text-decoration: none; }
   .profile-stats strong { color: var(--text); }
   .profile-head button.secondary { background: transparent; color: var(--text); border: 1px solid var(--border); }
   .profile-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 16px; align-items: start; }
   .profile-card { background: var(--surface); border: 1px solid var(--border); border-radius: 8px; padding: 16px; min-width: 0; }
   .profile-card h2 { font-size: 17px; margin: 0 0 12px; }
   .profile-item { padding: 10px 0; border-top: 1px solid var(--border); }
   .profile-item:first-of-type { border-top: none; padding-top: 0; }
   .profile-item-head { display: flex; justify-content: space-between; gap: 8px; }
   .profile-item p { margin: 6px 0 0; overflow-wrap: anywhere; }
   .profile-rating { color: var(--accent); font-weight: 700; white-space: nowrap; }
   .profile-muted { color: var(--muted); margin: 0; }
</style>
@endsection