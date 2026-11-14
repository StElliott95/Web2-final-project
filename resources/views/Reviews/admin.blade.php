@extends('layouts.app')
@section('app-content')
<h1>Review moderation</h1>
<table>
<thead>
<tr>
<th>User</th>
<th>Movie</th>
<th>Rating</th>
<th>Review</th>
<th>Sentiment</th>
<th>Status</th>
<th>Actions</th>
</tr>
</thead>
<tbody>
       @forelse($data as $row)
<tr>
<td>{{ $row->getUser->name }}</td>
<td>{{ $row->getMovie->title }}</td>
<td>{{ $row->rating }}/10</td>
<td>{{ $row->body }}</td>
<td>
    @if($row->sentiment_label)
        {{ ucfirst($row->sentiment_label) }}@if($row->sentiment_score !== null) ({{ number_format((float) $row->sentiment_score * 100, 0) }}%)@endif
    @else
        Not analyzed
    @endif
    @if($row->body)
        <form action="{{ route('integrations.review-sentiment', $row->id) }}" method="POST">
            @csrf
            <button type="submit">{{ $row->sentiment_label ? 'Re-analyze' : 'Analyze sentiment' }}</button>
        </form>
    @endif
</td>
<td>{{ $row->is_hidden ? 'Hidden' : 'Visible' }}</td>
<td>
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
<tr>
<td colspan="7">no reviews found</td>
</tr>
       @endforelse
</tbody>
</table>
@endsection
