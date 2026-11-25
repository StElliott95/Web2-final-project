@extends('layouts.app')
@section('title', 'Edit review')
@section('app-content')
<section class="review-form-card" aria-labelledby="edit-heading">
<h1 id="edit-heading">Edit my review of "{{ $row->getMovie->title }}"</h1>
<form action="{{ route('update-review', $row->id) }}" method="POST" class="review-form">
       @csrf
       @method('PUT')
<div class="review-form-rating">
<label for="rating">Rating</label>
<select id="rating" name="rating" required>
               @for($i = 10; $i >= 1; $i--)
<option value="{{ $i }}" @selected(old('rating', $row->rating) == $i)>{{ $i }}</option>
               @endfor
</select>
</div>
<label for="body">Review</label>
<textarea id="body" name="body" rows="3" maxlength="2000">{{ old('body', $row->body) }}</textarea>
<div class="review-form-actions">
<button type="submit">Save</button>
<a href="{{ route('list-reviews', $row->movie_id) }}">Cancel</a>
</div>
</form>
</section>
<style>
   .review-form-card { max-width: 560px; background: var(--surface); border: 1px solid var(--border); border-radius: 8px; padding: 16px; }
   .review-form-card h1 { font-size: 20px; margin: 0 0 12px; }
   .review-form { display: grid; gap: 8px; }
   .review-form-rating { display: flex; align-items: center; gap: 8px; }
   .review-form-rating select { width: auto; min-width: 80px; }
   .review-form textarea { resize: vertical; min-height: 72px; }
   .review-form-actions { display: flex; align-items: center; gap: 16px; }
</style>
@endsection