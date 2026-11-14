@extends('layouts.app')
@section('title',$list->title)
@section('head')@include('components.feature-style')@endsection
@section('app-content')
<div class="feature-heading"><div><span class="feature-tag">{{ $list->is_public ? 'Public' : 'Private' }} · By {{ $list->user->name }}</span><h1>{{ $list->title }}</h1><p>{{ $list->description }}</p></div>
@can('update',$list)<div class="feature-actions"><a class="btn" href="{{ route('lists.edit',$list) }}">Edit list</a><form method="post" action="{{ route('lists.destroy',$list) }}" onsubmit="return confirm('Delete this list permanently?')">@csrf @method('DELETE')<button class="danger">Delete list</button></form></div>@endcan</div>
@can('update',$list)@if($available->isNotEmpty())<form class="feature-panel feature-actions" method="post" action="{{ route('lists.movies.add',$list) }}">@csrf<label for="movie-id">Movie to add</label><select style="max-width:360px" id="movie-id" name="movie_id" required><option value="">Choose a movie</option>@foreach($available as $film)<option value="{{ $film->id }}">{{ $film->title }}</option>@endforeach</select><button>Add movie</button></form>@endif @endcan
<div class="feature-grid">@forelse($movies as $movie)<div>@include('Discovery.partials.movie-card')@can('update',$list)<form style="margin-top:8px" method="post" action="{{ route('lists.movies.remove',[$list,$movie]) }}">@csrf @method('DELETE')<button>Remove from list</button></form>@endcan</div>@empty<p class="feature-empty">This list has no movies yet.</p>@endforelse</div>{{ $movies->links('components.team-pagination') }}
@endsection
