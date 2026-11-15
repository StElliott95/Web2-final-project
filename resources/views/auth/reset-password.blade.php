@extends('layouts.app')
@section('title', 'Choose a new password')
@section('app-content')
@include('auth.partials.card-styles')
<section class="auth-card" aria-labelledby="auth-title">
    <p class="auth-eyebrow">ACCOUNT RECOVERY</p>
    <h1 id="auth-title">Choose a new password</h1>
    <p class="auth-intro">Set a new password for your account.</p>

    <form class="auth-form" action="{{ route('password.update') }}" method="post">
        @csrf
        <input type="hidden" name="token" value="{{ $token }}">

        <label for="email">Email address</label>
        <input id="email" name="email" type="email" required maxlength="255" autocomplete="email" value="{{ old('email', request('email')) }}">

        <label for="password">New password</label>
        <input id="password" name="password" type="password" required minlength="8" autocomplete="new-password" aria-describedby="password-help">
        <span id="password-help" class="auth-help">Use at least 8 characters, including a letter and a number.</span>

        <label for="password_confirmation">Confirm new password</label>
        <input id="password_confirmation" name="password_confirmation" type="password" required minlength="8" autocomplete="new-password">

        <button type="submit">Reset password</button>
    </form>
</section>
@endsection
