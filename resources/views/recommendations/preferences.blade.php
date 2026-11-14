@extends('layouts.app')
@section('title','Movie preferences')
@section('head')@include('components.feature-style')@endsection
@section('app-content')
<h1>Your movie preferences</h1><p class="muted">Choose favorite genres, actors, and directors from the saved catalogue.</p>
<form class="feature-panel feature-stack" method="post" action="{{ route('preferences.update') }}">@csrf @method('PUT')
@foreach($choices as $key=>$options)<fieldset><legend>Favorite {{ $key }}</legend><div class="feature-choices">@forelse($options as $option)<label class="feature-choice"><input type="checkbox" name="{{ $key }}[]" value="{{ $option->id }}" @checked(in_array($option->id,old($key,$preferences?->$key ?? [])))>{{ $option->name }}</label>@empty<p>No {{ $key }} are available yet.</p>@endforelse</div></fieldset>@endforeach
<button>Save preferences</button></form>
@endsection
