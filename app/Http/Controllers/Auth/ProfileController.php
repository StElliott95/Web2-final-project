<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function edit(Request $request): View
    {
        $user = $request->user()->load('socialAccounts');
        $isGithubSession = $request->session()->get('auth_method') === 'github';

        return view('auth.profile', compact('user', 'isGithubSession'));
    }

    public function update(Request $request): RedirectResponse
    {
        $user = $request->user();
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'bio' => ['nullable', 'string', 'max:500'],
            'current_password' => ['nullable', 'string', 'max:255'],
        ]);
        $email = strtolower(trim($data['email']));
        $emailChanged = $email !== strtolower($user->email);
        $isGithubSession = $request->session()->get('auth_method') === 'github';
        $currentPassword = $data['current_password'] ?? null;

        if (filled($currentPassword) && ! Hash::check($currentPassword, $user->password)) {
            return back()->withErrors([
                'current_password' => 'The current password is incorrect.',
            ])->withInput($request->except('current_password'));
        }

        if ($emailChanged && ! $isGithubSession && ! filled($currentPassword)) {
            return back()->withErrors([
                'current_password' => 'Enter your current password to change your email address.',
            ])->withInput($request->except('current_password'));
        }

        $user->fill([
            'name' => $data['name'],
            'email' => $email,
            'bio' => $data['bio'] ?? null,
        ]);

        if ($emailChanged) {
            $user->email_verified_at = null;
        }

        $user->save();

        return back()->with('success', 'Your profile has been updated.');
    }

    public function updatePassword(Request $request): RedirectResponse
    {
        $isGithubSession = $request->session()->get('auth_method') === 'github';
        $data = $request->validate([
            'current_password' => $isGithubSession ? ['nullable', 'string', 'max:255'] : ['required', 'current_password'],
            'password' => ['required', 'confirmed', Password::min(8)->letters()->numbers()],
        ]);

        if ($isGithubSession && filled($data['current_password'] ?? null)
            && ! Hash::check($data['current_password'], $request->user()->password)) {
            return back()->withErrors(['current_password' => 'The current password is incorrect.']);
        }

        $request->user()->forceFill([
            'password' => $data['password'],
            'remember_token' => Str::random(60),
        ])->save();
        $request->session()->regenerate();
        $request->session()->put('auth_method', 'password');

        return back()->with('success', 'Your password has been changed.');
    }
}
