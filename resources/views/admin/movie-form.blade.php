@extends('layouts.app')
@section('title','Edit catalogue')
@section('head')@include('components.feature-style')@endsection
@section('app-content')
@include('admin.nav')<h1>{{ $movie->exists ? 'Edit local movie' : 'Add local movie' }}</h1><p class="muted">Use the real TMDB identifier. A later TMDB synchronization may refresh local catalogue fields.</p>
<form class="feature-panel feature-stack" method="post" action="{{ $movie->exists ? route('admin.movies.update',$movie) : route('admin.movies.store') }}">@csrf @if($movie->exists)@method('PUT')@endif
<label>TMDB ID<input type="number" name="tmdb_id" required min="1" value="{{ old('tmdb_id',$movie->tmdb_id) }}"></label>
<label>Title<input name="title" required maxlength="255" value="{{ old('title',$movie->title) }}"></label>
<label>Overview<textarea name="overview" maxlength="10000" rows="4">{{ old('overview',$movie->overview) }}</textarea></label>
<div class="feature-two"><label>Release date<input type="date" name="release_date" value="{{ old('release_date',$movie->release_date?->format('Y-m-d')) }}"></label><label>Runtime in minutes<input type="number" min="1" max="600" name="runtime" value="{{ old('runtime',$movie->runtime) }}"></label></div>
<label>Catalogue rating (0–10)<input type="number" min="0" max="10" step="0.1" name="vote_average" value="{{ old('vote_average',$movie->vote_average) }}"></label>
<fieldset><legend>Genres</legend><div class="feature-choices">@foreach($genres as $genre)<label class="feature-choice"><input type="checkbox" name="genres[]" value="{{ $genre->id }}" @checked(in_array($genre->id,old('genres',$movie->genres->pluck('id')->all())))>{{ $genre->name }}</label>@endforeach</div></fieldset>
@foreach(['actors'=>'cast','directors'=>'crew'] as $key=>$type)<fieldset><legend>{{ ucfirst($key) }}</legend><div class="feature-choices">@foreach($people as $person)<label class="feature-choice"><input type="checkbox" name="{{ $key }}[]" value="{{ $person->id }}" @checked(in_array($person->id,old($key,$movie->credits->filter(fn($c)=>$c->credit_type===$type && ($type==='cast'||$c->job==='Director'))->pluck('person_id')->all())))>{{ $person->name }}</label>@endforeach</div></fieldset>@endforeach
<div class="feature-actions"><button>Save movie</button><a href="{{ route('admin.movies') }}">Cancel</a></div></form>
@endsection
