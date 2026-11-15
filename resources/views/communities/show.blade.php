@extends('layouts.app')
@section('title',$community->name)
@section('head')@include('components.feature-style')@endsection
@section('app-content')
<a href="{{ route('communities.index') }}">← All communities</a>
<div class="feature-heading"><div><h1>{{ $community->name }}</h1><p class="muted">{{ $community->description }}</p></div>
@if($joined)<span class="feature-tag">You are a member</span>@else<form method="post" action="{{ route('communities.join',$community) }}">@csrf<button>Join community</button></form>@endif</div>
<section class="feature-panel"><h2>General chat</h2><p>Catch up with the community, share what you are watching, and plan the next movie night.</p>
@if($joined && $chat)<a class="btn" href="{{ route('communities.room',[$community,$chat]) }}">Open general chat</a>@else<p class="muted">Join to read and send messages.</p>@endif</section>
<h2>Movie threads</h2><p class="muted">One conversation, one movie. {{ $threads->total() }} threads available.</p>
@if($joined)<details class="feature-panel" @if($errors->any()) open @endif><summary>Start a movie thread</summary><form class="feature-stack" method="post" action="{{ route('communities.threads.store',$community) }}">@csrf
<label for="thread-movie">Movie</label><select name="movie_id" id="thread-movie" required><option value="">Choose a movie</option>@foreach($movies as $movie)<option value="{{ $movie->id }}" @selected(old('movie_id') == $movie->id)>{{ $movie->title }}</option>@endforeach</select>
<label for="thread-title">Thread title</label><input id="thread-title" name="title" maxlength="150" value="{{ old('title') }}" placeholder="What would you like to discuss?" required><button>Create movie thread</button></form></details>@endif
<div class="feature-two">@forelse($threads as $thread)<article class="feature-panel"><span class="feature-tag">{{ $thread->movie->title }} · {{ $thread->messages_count }} messages</span><h3>{{ $thread->title }}</h3>@if($joined)<a href="{{ route('communities.room',[$community,$thread]) }}">Open thread →</a>@else<p class="muted">Join to read this conversation.</p>@endif</article>@empty<p class="feature-empty">No movie threads yet. Join and start the first conversation.</p>@endforelse</div>
{{ $threads->links('components.team-pagination') }}
@endsection
