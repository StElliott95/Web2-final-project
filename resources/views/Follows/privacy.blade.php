@extends('layouts.app')
@section('title', 'Privacy settings')
@section('app-content')
<section class="privacy-card" aria-labelledby="privacy-heading">
<h1 id="privacy-heading">What appears on my public profile</h1>
<p class="privacy-muted">Unchecked items are hidden from other users on your profile and in their feed. You always see your own activity.</p>
<form action="{{ route('update-privacy') }}" method="POST" class="privacy-form">
       @csrf
       @method('PUT')
<label class="privacy-option">
<input type="checkbox" name="show_reviews" value="1" @checked($user->show_reviews)>
<span>My reviews and ratings</span>
</label>
<label class="privacy-option">
<input type="checkbox" name="show_lists" value="1" @checked($user->show_lists)>
<span>My public movie lists</span>
</label>
<label class="privacy-option">
<input type="checkbox" name="show_watched" value="1" @checked($user->show_watched)>
<span>Movies I watched</span>
</label>
<div class="privacy-actions">
<button type="submit">Save</button>
<a href="{{ route('user-profile', $user->id) }}">View my profile</a>
</div>
</form>
</section>
<style>
   .privacy-card { max-width: 560px; background: var(--surface); border: 1px solid var(--border); border-radius: 8px; padding: 20px; }
   .privacy-card h1 { font-size: 22px; margin: 0 0 8px; }
   .privacy-muted { color: var(--muted); margin: 0 0 16px; }
   .privacy-form { display: grid; gap: 12px; }
   .privacy-option { display: flex; align-items: center; gap: 10px; cursor: pointer; }
   .privacy-option input { width: 18px; height: 18px; margin: 0; flex: 0 0 auto; }
   .privacy-actions { display: flex; align-items: center; gap: 16px; margin-top: 8px; }
</style>
@endsection