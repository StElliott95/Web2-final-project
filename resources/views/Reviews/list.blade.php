@extends('layouts.app')
@section('app-content')
<h1>{{ $movie->title }}</h1>
<h3>
    Average rating:
    {{ $average ? round($average, 1) : 'no ratings yet' }} / 10
    ({{ $data->count() }} reviews)
</h3>
<hr>

@if($myReview)
    <section aria-labelledby="my-review-heading">
        <h2 id="my-review-heading">My review: {{ $myReview->rating }}/10</h2>
        <p>{{ $myReview->body }}</p>
        @if($myReview->is_hidden)
            <p class="error">Your review was hidden by the admin.</p>
        @endif
        <p><strong>Sentiment:</strong> @include('Reviews.sentiment-result', ['sentimentReview' => $myReview])</p>
        <a href="{{ route('edit-review', $myReview->id) }}" class="btn">Edit</a>
        <form action="{{ route('delete-review', $myReview->id) }}" method="POST" class="inline">
            @csrf
            @method('DELETE')
            <button type="submit" class="danger">Delete</button>
        </form>
        @if(filled($myReview->body))
            <form action="{{ route('integrations.review-sentiment', $myReview) }}" method="POST" class="sentiment-action">
                @csrf
                <button type="submit">{{ $myReview->sentiment_label ? 'Analyze again' : 'Analyze sentiment' }}</button>
            </form>
            @include('Reviews.sentiment-disclosure')
        @else
            <p class="muted">Add review text to request sentiment analysis.</p>
        @endif
    </section>
@else
    <h2>Rate this movie</h2>
    <form action="{{ route('save-review', $movie->id) }}" method="POST">
        @csrf
        <label for="rating">Rating</label>
        <select id="rating" name="rating">
            @for($i = 10; $i >= 1; $i--)
                <option value="{{ $i }}">{{ $i }}</option>
            @endfor
        </select>
        <label for="body">Review (optional)</label>
        <textarea id="body" name="body" rows="4">{{ old('body') }}</textarea>
        <button type="submit">Submit</button>
    </form>
@endif

<hr>
<h2>All reviews</h2>
<div class="table-scroll">
    <table>
        <thead>
            <tr>
                <th scope="col">User</th>
                <th scope="col">Rating</th>
                <th scope="col">Review</th>
                <th scope="col">Sentiment</th>
                <th scope="col">Date</th>
            </tr>
        </thead>
        <tbody>
            @forelse($data as $row)
                <tr>
                    <td>{{ $row->getUser->name }}</td>
                    <td>{{ $row->rating }}/10</td>
                    <td>{{ $row->body }}</td>
                    <td>@include('Reviews.sentiment-result', ['sentimentReview' => $row])</td>
                    <td>{{ $row->created_at->diffForHumans() }}</td>
                </tr>
            @empty
                <tr><td colspan="5">No reviews found.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

<style>
    .sentiment-action { display: inline-block; margin: 12px 8px 0 0; }
    .sentiment-positive { color: var(--success); }
    .sentiment-negative { color: #ffaaaa; }
    .sentiment-neutral, .sentiment-pending, .muted { color: var(--muted); }
    .table-scroll { max-width: 100%; overflow-x: auto; }
    .table-scroll table { min-width: 640px; }
</style>
@endsection
