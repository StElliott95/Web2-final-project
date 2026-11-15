@extends('layouts.app')
@section('title', 'Sign in')
@section('app-content')
@include('auth.partials.card-styles')
<section class="auth-card" aria-labelledby="auth-title">
    <p class="auth-eyebrow">WELCOME BACK</p>
    <h1 id="auth-title">Sign in</h1>
    <p class="auth-intro">Sign in to manage your movie profile and collections.</p>

    <form class="auth-form" action="{{ route('login.store') }}" method="post">
        @csrf
        <label for="email">Email address</label>
        <input id="email" name="email" type="email" required maxlength="255" autocomplete="email" value="{{ old('email') }}">

        <label for="password">Password</label>
        <input id="password" name="password" type="password" required autocomplete="current-password">

        <label class="auth-checkbox" for="remember">
            <input id="remember" name="remember" type="checkbox" value="1" {{ old('remember') ? 'checked' : '' }}>
            <span>Keep me signed in</span>
        </label>

        <button type="submit">Sign in</button>
    </form>

    @include('auth.partials.social-buttons')

    <div class="auth-links">
        <a href="{{ route('password.request') }}">Forgot your password?</a>
        <span>New here? <a href="{{ route('register') }}">Create an account</a></span>
    </div>
</section>
@endsection
