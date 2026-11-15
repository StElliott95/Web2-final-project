@extends('layouts.app')
@section('app-content')
<h1>Review moderation</h1>
@include('Reviews.sentiment-disclosure')
<div class="table-scroll">
    <table>
        <thead>
            <tr>
                <th scope="col">User</th>
                <th scope="col">Movie</th>
                <th scope="col">Rating</th>
                <th scope="col">Review</th>
                <th scope="col">Sentiment</th>
                <th scope="col">Status</th>
                <th scope="col">Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($data as $row)
                <tr>
                    <td>{{ $row->getUser->name }}</td>
                    <td>{{ $row->getMovie->title }}</td>
                    <td>{{ $row->rating }}/10</td>
                    <td>{{ $row->body }}</td>
                    <td>@include('Reviews.sentiment-result', ['sentimentReview' => $row])</td>
                    <td>{{ $row->is_hidden ? 'Hidden' : 'Visible' }}</td>
                    <td>
                        @if(filled($row->body))
                            <form action="{{ route('integrations.review-sentiment', $row) }}" method="POST" class="inline">
                                @csrf
                                <button type="submit">{{ $row->sentiment_label ? 'Analyze again' : 'Analyze sentiment' }}</button>
                            </form>
                        @endif
                        <form action="{{ route('toggle-review', $row->id) }}" method="POST" class="inline">
                            @csrf
                            @method('PUT')
                            <button type="submit">{{ $row->is_hidden ? 'Show' : 'Hide' }}</button>
                        </form>
                        <form action="{{ route('admin-delete-review', $row->id) }}" method="POST" class="inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="danger">Delete</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="7">No reviews found.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
<style>
    .sentiment-positive { color: var(--success); }
    .sentiment-negative { color: #ffaaaa; }
    .sentiment-neutral, .sentiment-pending { color: var(--muted); }
    .table-scroll { max-width: 100%; overflow-x: auto; }
    .table-scroll table { min-width: 820px; }
</style>
@endsection
