@extends('layouts.app')
@section('title','Communities')
@section('head')@include('components.feature-style')@endsection
@section('app-content')
<div class="feature-heading"><div><span class="feature-tag">WATCH · TALK · CONNECT</span><h1>Communities</h1><p class="muted">Find your people. Join a chat or explore a movie thread.</p></div><a class="btn" href="{{ route('communities.create') }}">Create community</a></div>
<form class="feature-actions" method="get"><label for="community-search">Search communities</label><input style="max-width:360px" id="community-search" name="q" value="{{ request('q') }}"><button>Search</button>@if(request('q'))<a href="{{ route('communities.index') }}">Clear</a>@endif</form>
<p class="muted">{{ $communities->total() }} communities · Chats and movie conversations, separate from your collections.</p>
{{ $communities->links('components.team-pagination') }}
<div class="feature-two">@forelse($communities as $community)<article class="feature-panel"><span class="feature-tag">{{ $community->members_count }} members · {{ $community->threads_count }} movie threads</span><h2><a href="{{ route('communities.show',$community) }}">{{ $community->name }}</a></h2><p>{{ Str::limit($community->description,160) }}</p><a href="{{ route('communities.show',$community) }}">Explore community →</a></article>@empty<p class="feature-empty">No communities found. Start a community or try another search.</p>@endforelse</div>
{{ $communities->links('components.team-pagination') }}
@endsection
