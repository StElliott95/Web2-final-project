@extends('layouts.app')
@section('title','Create community')
@section('head')@include('components.feature-style')@endsection
@section('app-content')
<a href="{{ route('communities.index') }}">← Communities</a><h1>Create a community</h1><p class="muted">Give movie fans a place to chat and start movie threads. Community names and thread titles are public; conversations are for joined members.</p>
<form method="post" action="{{ route('communities.store') }}" class="feature-panel feature-stack">@csrf
<label for="community-name">Name</label><input id="community-name" name="name" maxlength="120" value="{{ old('name') }}" required>
<label for="community-description">Description</label><textarea id="community-description" name="description" maxlength="2000" rows="4">{{ old('description') }}</textarea>
<button>Create community</button></form>
@endsection
