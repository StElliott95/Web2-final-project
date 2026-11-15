@extends('layouts.app')
@section('title', 'Reset your password')
@section('app-content')
@include('auth.partials.card-styles')
<section class="auth-card" aria-labelledby="auth-title">
    <p class="auth-eyebrow">ACCOUNT RECOVERY</p>
    <h1 id="auth-title">Forgot your password?</h1>
    <p class="auth-intro">Enter the email address for your account. If it is registered, we will send a reset link.</p>

    <form class="auth-form" action="{{ route('password.email') }}" method="post">
        @csrf
        <label for="email">Email address</label>
        <input id="email" name="email" type="email" required maxlength="255" autocomplete="email" value="{{ old('email') }}">
        <button type="submit">Send reset link</button>
    </form>

    <div class="auth-links"><a href="{{ route('login') }}">Back to sign in</a></div>
</section>
@endsection
