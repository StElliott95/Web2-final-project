@extends('layouts.app')
@section('app-content')
<h2>Edit my review of "{{ $row->getMovie->title }}"</h2>
<form action="{{ route('update-review', $row->id) }}" method="POST">
       @csrf
       @method('PUT')
<label>Rating</label>
<select name="rating">
           @for($i = 10; $i >= 1; $i--)
<option value="{{ $i }}" @if($row->rating == $i) selected @endif>{{ $i }}</option>
           @endfor
</select>
<label>Review</label>
<textarea name="body" rows="4">{{ $row->body }}</textarea>
<button type="submit">Save</button>
<a href="{{ route('list-reviews', $row->movie_id) }}">Cancel</a>
</form>
@endsection