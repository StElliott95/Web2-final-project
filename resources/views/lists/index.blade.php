@extends('layouts.app')
@section('title',$public ? 'Community lists' : 'My lists')
@section('head')@include('components.feature-style')@endsection
@section('app-content')
<div class="feature-heading"><div><h1>{{ $public ? 'Community lists' : 'My movie lists' }}</h1><p class="muted">{{ $public ? 'Browse public collections shared by members.' : 'Create a collection for yourself or share it with others.' }}</p></div><a class="btn" href="{{ route('lists.create') }}">Create list</a></div>
@if($public)<form class="feature-actions"><label for="list-search">Search public lists</label><input style="max-width:360px" id="list-search" name="q" value="{{ request('q') }}"><button>Search</button></form>@endif
<div class="feature-two">@forelse($lists as $list)<article class="feature-panel"><span class="feature-tag">{{ $list->is_public ? 'Public' : 'Private' }} · {{ $list->movies_count }} movies</span><h2><a href="{{ route('lists.show',$list) }}">{{ $list->title }}</a></h2><p>{{ Str::limit($list->description,160) }}</p>@if($public)<p class="muted">By {{ $list->user->name }}</p>@endif</article>@empty<p class="feature-empty">No lists found. Create your first collection or try a different search.</p>@endforelse</div>{{ $lists->links('components.team-pagination') }}
@endsection
