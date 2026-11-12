@extends('layouts.app')
@section('title','Administration')
@section('head')@include('components.feature-style')@endsection
@section('app-content')
@include('admin.nav')
<h1>Administrator dashboard</h1><p class="muted">Live statistics from the shared movie platform.</p>
<div class="feature-stats">@foreach($counts as $label=>$count)<div class="feature-stat">{{ $label }}<strong>{{ $count }}</strong></div>@endforeach</div>
<div class="feature-two"><section class="feature-panel"><h2>Most watched movies</h2><p class="muted">Unique viewers per movie</p>@forelse($watched as $movie)<p><a href="{{ route('movies.show',$movie->tmdb_id) }}">{{ $movie->title }}</a> · {{ $movie->total }} viewers</p>@empty<p>No watch history yet.</p>@endforelse</section>
<section class="feature-panel"><h2>Highest community ratings</h2><p class="muted">Visible reviews only · out of 10</p>@forelse($rated as $movie)<p><a href="{{ route('movies.show',$movie->tmdb_id) }}">{{ $movie->title }}</a> · {{ number_format($movie->get_reviews_avg_rating,1) }}/10</p>@empty<p>No visible reviews yet.</p>@endforelse</section></div>
<div class="feature-two"><section class="feature-panel"><h2>Popular genres</h2><p class="muted">Unique movie/viewer pairs contribute to each genre.</p>@forelse($genres as $genre)<p>{{ $genre->name }} · {{ $genre->total }} watches</p>@empty<p>No genre activity yet.</p>@endforelse</section>
<section class="feature-panel"><h2>Recent user activity</h2>@forelse($activities as $activity)<p><strong>{{ $activity->user?->name ?? 'Deleted user' }}</strong> · {{ $activity->action }} {{ $activity->subject }}<br><small class="muted">{{ $activity->created_at->diffForHumans() }}</small></p>@empty<p>No activity recorded yet.</p>@endforelse</section></div>
@endsection
