@extends('layouts.app')
@section('title','User activity')
@section('head')@include('components.feature-style')@endsection
@section('app-content')
@include('admin.nav')<h1>User activity</h1><p class="muted">Recorded changes and sign-ins. Times are UTC.</p>
<div class="feature-table"><table><thead><tr><th>User</th><th>Action</th><th>Subject</th><th>Time</th></tr></thead><tbody>@forelse($activities as $activity)<tr><td>{{ $activity->user?->name ?? 'Deleted user' }}</td><td>{{ $activity->action }}</td><td>{{ $activity->subject }}</td><td>{{ $activity->created_at->format('Y-m-d H:i') }}</td></tr>@empty<tr><td colspan="4">No recorded activity yet.</td></tr>@endforelse</tbody></table></div>{{ $activities->links('components.team-pagination') }}
@endsection
