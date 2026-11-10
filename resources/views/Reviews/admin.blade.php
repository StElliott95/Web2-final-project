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
<td colspan="6">no reviews found</td>
</tr>
       @endforelse
</tbody>
</table>
@endsection
