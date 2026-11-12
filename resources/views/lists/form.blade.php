@extends('layouts.app')
@section('title',$list->exists ? 'Edit list' : 'Create list')
@section('head')@include('components.feature-style')@endsection
@section('app-content')
<h1>{{ $list->exists ? 'Edit list' : 'Create a movie list' }}</h1>
<form class="feature-panel feature-stack" method="post" action="{{ $list->exists ? route('lists.update',$list) : route('lists.store') }}">@csrf @if($list->exists)@method('PUT')@endif
<label>Title<input name="title" value="{{ old('title',$list->title) }}" required maxlength="120"></label>
<label>Description<textarea name="description" rows="4" maxlength="2000">{{ old('description',$list->description) }}</textarea></label>
<label>Visibility<select name="is_public"><option value="0" @selected(!old('is_public',$list->is_public))>Private — only you</option><option value="1" @selected(old('is_public',$list->is_public))>Public — all visitors</option></select></label>
<div class="feature-actions"><button>Save list</button><a href="{{ route('lists.index') }}">Cancel</a></div></form>
@endsection
