@extends('layouts.app')
@section('title','For you')
@section('head')@include('components.feature-style')@endsection
@section('app-content')
<div class="feature-heading"><div><h1>Recommended for you</h1><p class="muted">Your favorites come first. Viewing history and ratings refine the order.</p></div><a class="btn" href="{{ route('preferences.edit') }}">Edit preferences</a></div>
<section class="feature-panel" aria-label="Saved preferences">
<h2>Your saved favorites</h2>
@foreach($favorites as $category=>$names)<p><strong>{{ ucfirst($category) }}:</strong> {{ $names->isEmpty() ? 'None selected' : $names->join(', ') }}</p>@endforeach
<p class="muted">{{ $recommendations->total() }} unseen movies available · {{ $matchingCount }} match your favorites.</p>
@if(collect($favorites)->contains(fn($names)=>$names->isNotEmpty()) && $matchingCount === 0)<p>No unseen saved movies match these favorites yet. Showing other suggestions until matching titles are added to the catalogue.</p>@endif
<p class="muted">Preferences change the order of available movies; they do not import new titles. Watched and rated movies stay excluded.</p>
</section>
{{ $recommendations->links('components.team-pagination') }}
<div class="feature-grid">@forelse($recommendations as $item)<article>@include('Discovery.partials.movie-card',['movie'=>$item['movie']])<ul class="muted">@foreach($item['reasons'] as $reason)<li>{{ $reason }}</li>@endforeach</ul></article>@empty<p class="feature-empty">No new recommendations yet. Browse movies or return when more titles are saved.</p>@endforelse</div>
{{ $recommendations->links('components.team-pagination') }}
@endsection
