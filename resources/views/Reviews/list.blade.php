@extends('layouts.app')
@section('title', $movie->title.' reviews')
@section('app-content')
<div class="reviews-page">
<header class="reviews-head">
<div>
<p class="reviews-eyebrow">REVIEWS</p>
<h1>{{ $movie->title }}</h1>
</div>
<div class="avg-box" aria-label="Average rating">
<strong>{{ $average ? number_format($average, 1) : '–' }}</strong><span>/10</span>
<small>{{ $data->count() }} {{ \Illuminate\Support\Str::plural('review', $data->count()) }}</small>
</div>
</header>
   {{-- positive / negative summary, only for signed in users --}}
   @auth
       @include('Reviews.sentiment-summary', ['summary' => $sentiment])
   @endauth
   @if($myReview)
       {{-- I already reviewed this movie --}}
<section class="review-card my-review" aria-labelledby="my-review-heading">
<header class="review-card-head">
<h2 id="my-review-heading">Your review</h2>
<span class="review-rating" aria-label="Rated {{ $myReview->rating }} out of 10">★ {{ $myReview->rating }}<small>/10</small></span>
</header>
           @if(filled($myReview->body))
<p class="review-body">{{ $myReview->body }}</p>
           @else
<p class="sentiment-muted">Rating only, no written review.</p>
           @endif
           @if($myReview->is_hidden)
<p class="error">Your review was hidden by the admin.</p>
           @endif
<p class="review-sentiment"><strong>Sentiment:</strong> @include('Reviews.sentiment-result', ['sentimentReview' => $myReview])</p>
<div class="review-actions">
<a href="{{ route('edit-review', $myReview->id) }}" class="btn">Edit</a>
<form action="{{ route('delete-review', $myReview->id) }}" method="POST" class="inline">
                   @csrf
                   @method('DELETE')
<button type="submit" class="danger">Delete</button>
</form>
               @if(filled($myReview->body))
<form action="{{ route('integrations.review-sentiment', $myReview) }}" method="POST" class="inline">
                       @csrf
<button type="submit">{{ $myReview->sentiment_label ? 'Analyze again' : 'Analyze sentiment' }}</button>
</form>
               @endif
</div>
           @if(filled($myReview->body))
               @include('Reviews.sentiment-disclosure')
           @endif
</section>
   @else
       {{-- add a new review (small form) --}}
<section class="review-form-card" aria-labelledby="rate-heading">
<h2 id="rate-heading">Rate this movie</h2>
<form action="{{ route('save-review', $movie->id) }}" method="POST" class="review-form">
               @csrf
<div class="review-form-rating">
<label for="rating">Rating</label>
<select id="rating" name="rating" required>
                       @for($i = 10; $i >= 1; $i--)
<option value="{{ $i }}" @selected(old('rating') == $i)>{{ $i }}</option>
                       @endfor
</select>
</div>
<label for="body">Review <span class="sentiment-muted">(optional)</span></label>
<textarea id="body" name="body" rows="3" maxlength="2000" placeholder="What did you think?">{{ old('body') }}</textarea>
<button type="submit">Post review</button>
</form>
</section>
   @endif
<h2 class="reviews-title">All reviews</h2>
<div class="review-grid">
       @forelse($data as $row)
<article class="review-card">
<header class="review-card-head">
<span class="review-avatar" aria-hidden="true">{{ strtoupper(mb_substr($row->getUser->name, 0, 1)) }}</span>
<div class="review-author">
<strong>{{ $row->getUser->name }}</strong>
                       @if($row->user_id == auth()->id())<span class="review-you">You</span>@endif
<time datetime="{{ $row->created_at->toIso8601String() }}">{{ $row->created_at->diffForHumans() }}</time>
</div>
<span class="review-rating" aria-label="Rated {{ $row->rating }} out of 10">★ {{ $row->rating }}<small>/10</small></span>
</header>
               @if(filled($row->body))
<p class="review-body">{{ $row->body }}</p>
               @else
<p class="sentiment-muted">Rating only, no written review.</p>
               @endif
               @if($row->sentiment_label)
<footer class="review-sentiment">@include('Reviews.sentiment-result', ['sentimentReview' => $row])</footer>
               @endif
</article>
       @empty
<p class="review-empty">No reviews yet. Be the first to rate this movie.</p>
       @endforelse
</div>
</div>
<style>
   .reviews-head { display: flex; justify-content: space-between; align-items: flex-end; gap: 16px; margin-bottom: 24px; }
   .reviews-head h1 { margin: 0; font-size: clamp(26px, 4vw, 38px); }
   .reviews-eyebrow { margin: 0 0 4px; color: var(--accent); font-size: 12px; letter-spacing: .08em; font-weight: 700; }
   .avg-box { text-align: right; line-height: 1.1; }
   .avg-box strong { font-size: 34px; color: var(--accent); }
   .avg-box span { color: var(--muted); }
   .avg-box small { display: block; color: var(--muted); margin-top: 4px; }
   /* small posting form */
   .review-form-card { max-width: 560px; background: var(--surface); border: 1px solid var(--border); border-radius: 8px; padding: 16px; margin-bottom: 32px; }
   .review-form-card h2 { font-size: 17px; margin: 0 0 12px; }
   .review-form { display: grid; gap: 8px; }
   .review-form-rating { display: flex; align-items: center; gap: 8px; }
   .review-form-rating select { width: auto; min-width: 80px; }
   .review-form textarea { resize: vertical; min-height: 72px; }
   .review-form button { justify-self: start; }
   /* review cards */
   .reviews-title { font-size: 20px; margin: 0 0 16px; }
   .review-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 16px; }
   .review-card { background: var(--surface); border: 1px solid var(--border); border-radius: 8px; padding: 16px; display: flex; flex-direction: column; gap: 8px; min-width: 0; }
   .my-review { max-width: 560px; margin-bottom: 32px; border-color: var(--accent); }
   .my-review h2 { font-size: 17px; margin: 0; flex: 1; }
   .review-card-head { display: flex; align-items: center; gap: 8px; }
   .review-avatar { width: 36px; height: 36px; border-radius: 50%; background: var(--surface-raised); color: var(--accent); display: grid; place-items: center; font-weight: 700; flex: 0 0 auto; }
   .review-author { flex: 1; min-width: 0; display: flex; flex-wrap: wrap; align-items: baseline; gap: 0 8px; }
   .review-author strong { overflow-wrap: anywhere; }
   .review-author time { color: var(--muted); font-size: 13px; width: 100%; }
   .review-you { font-size: 12px; color: #171717; background: var(--accent); border-radius: 99px; padding: 0 8px; }
   .review-rating { color: var(--accent); font-weight: 700; white-space: nowrap; }
   .review-rating small { color: var(--muted); font-weight: 400; }
   .review-body { margin: 0; overflow-wrap: anywhere; white-space: pre-line; }
   .review-sentiment { margin: 0; font-size: 14px; }
   .review-actions { display: flex; flex-wrap: wrap; gap: 8px; }
   .review-actions .btn, .review-actions button { font: 700 14px/1.4 Arial, sans-serif; padding: 8px 14px; }
   .my-review .sentiment-disclosure { margin-bottom: 0; }
   .review-empty { grid-column: 1 / -1; padding: 24px; border: 1px dashed var(--border); border-radius: 8px; color: var(--muted); }
   .sentiment-pending { color: var(--muted); }
   @media (max-width: 520px) {
       .reviews-head { flex-direction: column; align-items: flex-start; }
       .avg-box { text-align: left; }
   }
</style>
@endsection