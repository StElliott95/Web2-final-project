@extends('layouts.app')
@section('title','Manage movies')
@section('head')@include('components.feature-style')@endsection
@section('app-content')
@include('admin.nav')<div class="feature-heading"><h1>Local movie catalogue</h1><a class="btn" href="{{ route('admin.movies.create') }}">Add movie</a></div>
<form class="feature-actions"><label for="movie-search">Search title</label><input style="max-width:360px" id="movie-search" name="q" value="{{ request('q') }}"><button>Search</button></form>
<div class="feature-table"><table><thead><tr><th>Movie</th><th>TMDB ID</th><th>Actions</th></tr></thead><tbody>@forelse($movies as $movie)<tr><td><a href="{{ route('movies.show',$movie->tmdb_id) }}">{{ $movie->title }}</a></td><td>{{ $movie->tmdb_id }}</td><td><div class="feature-actions"><a href="{{ route('admin.movies.edit',$movie) }}">Edit</a><form method="post" action="{{ route('admin.movies.delete',$movie) }}" onsubmit="return confirm('Delete this local movie and its reviews, watch history, and list memberships?')">@csrf @method('DELETE')<button class="danger">Delete</button></form></div></td></tr>@empty<tr><td colspan="3">No movies found.</td></tr>@endforelse</tbody></table></div>{{ $movies->links('components.team-pagination') }}
@endsection
