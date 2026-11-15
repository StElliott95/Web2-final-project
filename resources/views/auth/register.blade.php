@extends('layouts.app')
@section('title', 'Create account')
@section('app-content')
@include('auth.partials.card-styles')
<section class="auth-card" aria-labelledby="auth-title">
    <p class="auth-eyebrow">JOIN THE COMMUNITY</p>
    <h1 id="auth-title">Create your account</h1>
    <p class="auth-intro">Save films, track what you watch, and join movie discussions.</p>

    <form class="auth-form" action="{{ route('register.store') }}" method="post">
        @csrf
        <label for="name">Name</label>
        <input id="name" name="name" type="text" required maxlength="100" autocomplete="name" value="{{ old('name') }}">

        <label for="email">Email address</label>
        <input id="email" name="email" type="email" required maxlength="255" autocomplete="email" value="{{ old('email') }}">

        <label for="password">Password</label>
        <input id="password" name="password" type="password" required minlength="8" autocomplete="new-password" aria-describedby="password-help">
        <span id="password-help" class="auth-help">Use at least 8 characters, including a letter and a number.</span>

        <label for="password_confirmation">Confirm password</label>
        <input id="password_confirmation" name="password_confirmation" type="password" required minlength="8" autocomplete="new-password">

        <button type="submit">Create account</button>
    </form>

    @include('auth.partials.social-buttons')

    <div class="auth-links"><span>Already registered? <a href="{{ route('login') }}">Sign in</a></span></div>
</section>
@endsection
