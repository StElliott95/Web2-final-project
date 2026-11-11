@extends('layouts.app')
@section('app-content')
<h1>My watchlist</h1>
<a href="{{ route('watch-history') }}" class="btn">Watch history</a>
<hr>
   {{-- add a movie --}}
<form action="{{ route('add-watchlist') }}" method="POST">
       @csrf
<select name="movie_id" required>
<option value="">-- choose a movie --</option>
           @foreach($movies as $movie)
<option value="{{ $movie->id }}">{{ $movie->title }}</option>
           @endforeach
</select>
<button type="submit">Add to watchlist</button>
</form>
<h3>To watch ({{ $toWatch->count() }})</h3>
<table>
<thead>
<tr><th>Movie</th><th>Actions</th></tr>
</thead>
<tbody>
       @forelse($toWatch as $row)
<tr>
<td>{{ $row->getMovie->title }}</td>
<td>
<form action="{{ route('watched-watchlist', $row->id) }}" method="POST" class="inline">
                       @csrf
                       @method('PUT')
<button type="submit">✓ Watched</button>
</form>
<form action="{{ route('remove-watchlist', $row->id) }}" method="POST" class="inline">
                       @csrf
                       @method('DELETE')
<button type="submit" class="danger">Remove</button>
</form>
</td>
</tr>
       @empty
<tr><td colspan="2">your watchlist is empty</td></tr>
       @endforelse
</tbody>
</table>
<h3>Watched ({{ $watched->count() }})</h3>
<table>
<thead>
<tr><th>Movie</th><th>Watched</th><th>Actions</th></tr>
</thead>
<tbody>
       @forelse($watched as $row)
<tr>
<td>{{ $row->getMovie->title }}</td>
<td>{{ $row->watched_at->diffForHumans() }}</td>
<td>
<form action="{{ route('unwatched-watchlist', $row->id) }}" method="POST" class="inline">
                       @csrf
                       @method('PUT')
<button type="submit">Not watched</button>
</form>
<form action="{{ route('remove-watchlist', $row->id) }}" method="POST" class="inline">
                       @csrf
                       @method('DELETE')
<button type="submit" class="danger">Remove</button>
</form>
</td>
</tr>
       @empty
<tr><td colspan="3">no watched movies yet</td></tr>
       @endforelse
</tbody>
</table>
@endsection