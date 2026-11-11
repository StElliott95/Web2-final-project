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
       {{-- I already reviewed this movie --}}
<h3>My review: {{ $myReview->rating }}/10</h3>
<p>{{ $myReview->body }}</p>
       @if($myReview->is_hidden)
<p class="error">Your review was hidden by the admin</p>
       @endif
<a href="{{ route('edit-review', $myReview->id) }}" class="btn">Edit</a>
<form action="{{ route('delete-review', $myReview->id) }}" method="POST" class="inline">
           @csrf
           @method('DELETE')
<button type="submit" class="danger">Delete</button>
</form>
   @else
       {{-- add a new review --}}
<h3>Rate this movie</h3>
<form action="{{ route('save-review', $movie->id) }}" method="POST">
           @csrf
<label>Rating</label>
<select name="rating">
               @for($i = 10; $i >= 1; $i--)
<option value="{{ $i }}">{{ $i }}</option>
               @endfor
</select>
<label>Review (optional)</label>
<textarea name="body" rows="4">{{ old('body') }}</textarea>
<button type="submit">Submit</button>
</form>
   @endif
<hr>
<h3>All reviews</h3>
<table>
<thead>
<tr>
<th>User</th>
<th>Rating</th>
<th>Review</th>
<th>Date</th>
</tr>
</thead>
<tbody>
       @forelse($data as $row)
<tr>
<td>{{ $row->getUser->name }}</td>
<td>{{ $row->rating }}/10</td>
<td>{{ $row->body }}</td>
<td>{{ $row->created_at->diffForHumans() }}</td>
</tr>
       @empty
<tr>
<td colspan="4">no reviews found</td>
</tr>
       @endforelse
</tbody>
</table>
@endsection
has context menu