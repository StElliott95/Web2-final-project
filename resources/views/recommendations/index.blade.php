@extends('layouts.app')
@section('title','For you')
@section('head')@include('components.feature-style')@endsection
@section('app-content')
<div class="feature-heading"><div><h1>Recommended for you</h1><p class="muted">Based on your favorites, watched movies, and 1–10 ratings.</p></div><a class="btn" href="{{ route('preferences.edit') }}">Edit preferences</a></div>
<div class="feature-grid">@forelse($recommendations as $item)<article>@include('Discovery.partials.movie-card',['movie'=>$item['movie']])<ul class="muted">@foreach($item['reasons'] as $reason)<li>{{ $reason }}</li>@endforeach</ul></article>@empty<p class="feature-empty">No new recommendations yet. Browse movies or return when more titles are saved.</p>@endforelse</div>
@endsection
