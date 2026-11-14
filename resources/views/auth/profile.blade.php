@extends('layouts.app')
@section('title', 'Your profile')
@section('app-content')
@include('auth.partials.card-styles')
<section class="auth-card" aria-labelledby="profile-title">
    <p class="auth-eyebrow">ACCOUNT SETTINGS</p>
    <h1 id="profile-title">Your profile</h1>
    <p class="auth-intro">Update the details shown on your account.</p>
    @if($user->socialAccounts->contains('provider', 'github'))
        <p class="auth-help" role="status">GitHub sign-in is connected to this account.</p>
    @endif

    <form class="auth-form" action="{{ route('profile.update') }}" method="post">
        @csrf
        @method('PUT')
        <label for="name">Name</label>
        <input id="name" name="name" type="text" required maxlength="100" autocomplete="name" value="{{ old('name', $user->name) }}">

        <label for="email">Email address</label>
        <input id="email" name="email" type="email" required maxlength="255" autocomplete="email" value="{{ old('email', $user->email) }}">
        <span class="auth-help">Changing your email requires your current password.</span>

        <label for="bio">About you</label>
        <textarea id="bio" name="bio" maxlength="500">{{ old('bio', $user->bio) }}</textarea>

        <label for="current_password_profile">Current password</label>
        <input id="current_password_profile" name="current_password" type="password" autocomplete="current-password">

        <button type="submit">Save profile</button>
    </form>

    <hr class="auth-divider">

    <h2 class="auth-section-title">Change password</h2>
    <form class="auth-form" action="{{ route('profile.password.update') }}" method="post">
        @csrf
        @method('PUT')
        <label for="current_password">Current password</label>
        <input id="current_password" name="current_password" type="password" required autocomplete="current-password">

        <label for="new_password">New password</label>
        <input id="new_password" name="password" type="password" required minlength="8" autocomplete="new-password" aria-describedby="new-password-help">
        <span id="new-password-help" class="auth-help">Use at least 8 characters, including a letter and a number.</span>

        <label for="password_confirmation">Confirm new password</label>
        <input id="password_confirmation" name="password_confirmation" type="password" required minlength="8" autocomplete="new-password">

        <button type="submit">Change password</button>
    </form>
</section>
@endsection
