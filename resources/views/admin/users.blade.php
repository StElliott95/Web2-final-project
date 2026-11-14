@extends('layouts.app')
@section('title','Manage users')
@section('head')@include('components.feature-style')@endsection
@section('app-content')
@include('admin.nav')<h1>User management</h1>
<form class="feature-actions"><label for="user-search">Search name or email</label><input style="max-width:360px" id="user-search" name="q" value="{{ request('q') }}"><button>Search</button></form>
<div class="feature-table"><table><thead><tr><th>User</th><th>Access</th><th>Remove</th></tr></thead><tbody>@forelse($users as $user)<tr><td>{{ $user->name }}<br><small>{{ $user->email }}</small></td><td>@if($user->id===auth()->id())Your administrator account @else<form class="feature-actions" method="post" action="{{ route('admin.users.update',$user) }}">@csrf @method('PUT')
<label>Role<select name="role"><option value="user" @selected($user->role==='user')>Member</option><option value="admin" @selected($user->role==='admin')>Administrator</option></select></label>
<label>Status<select name="is_active"><option value="1" @selected($user->is_active)>Active</option><option value="0" @selected(!$user->is_active)>Suspended</option></select></label><button>Save</button></form>@endif</td><td>@if($user->id!==auth()->id())<form method="post" action="{{ route('admin.users.delete',$user) }}" onsubmit="return confirm('Permanently delete this user and their dependent records?')">@csrf @method('DELETE')<button class="danger">Delete</button></form>@endif</td></tr>@empty<tr><td colspan="3">No users found.</td></tr>@endforelse</tbody></table></div>{{ $users->links('components.team-pagination') }}
@endsection
