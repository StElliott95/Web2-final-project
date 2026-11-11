@extends('layouts.app')

@section('app-content')
<h1>Watch history</h1>
<a href="{{ route('list-watchlist') }}" class="btn">Back to watchlist</a>
<table>
<thead>
<tr><th>Movie</th><th>Watched on</th></tr>
</thead>
<tbody>

        @forelse($data as $row)
<tr>
<td>{{ $row->getMovie->title }}</td>
<td>{{ $row->watched_at->format('d M Y, H:i') }}</td>
</tr>

        @empty
<tr><td colspan="2">no history yet</td></tr>

        @endforelse
</tbody>
</table>

@endsection
 