@extends('layouts.app')
@section('title','Sign in')
@section('app-content')
<section style="max-width:480px;margin:32px auto"><h1>Sign in</h1><p>Use your team account to access your movie collections.</p>
<form action="{{ route('login.store') }}" method="post">@csrf
<p><label for="email">Email</label><input id="email" name="email" type="email" required value="{{ old('email') }}" autocomplete="email"></p>
<p><label for="password">Password</label><input id="password" name="password" type="password" required autocomplete="current-password"></p>
<button>Sign in</button></form></section>
@endsection
