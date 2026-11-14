<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\SocialAccount;
use App\Models\User;
use App\Services\GitHubOAuthService;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;

class SocialAuthController extends Controller
{
    public function redirect(Request $request, GitHubOAuthService $github): RedirectResponse
    {
        if (! config('services.github_oauth.client_id') || ! config('services.github_oauth.client_secret')) {
            return redirect()->route('login')->with('error', 'GitHub sign-in is not configured yet.');
        }

        $state = Str::random(48);
        $request->session()->put('oauth.github.state', $state);

        return redirect()->away($github->authorizationUrl($state));
    }

    public function callback(Request $request, GitHubOAuthService $github): RedirectResponse
    {
        if ($request->query('error')) {
            $request->session()->forget('oauth.github.state');

            return redirect()->route('login')->with('error', 'GitHub sign-in was cancelled.');
        }

        $code = $request->query('code');
        $state = $request->query('state');
        $expectedState = $request->session()->pull('oauth.github.state');

        if (! is_string($code) || $code === '' || ! is_string($state)
            || ! is_string($expectedState) || ! hash_equals($expectedState, $state)) {
            return redirect()->route('login')->withErrors(['email' => 'GitHub sign-in could not be verified. Please try again.']);
        }

        try {
            $identity = $github->identity($code);
            $user = DB::transaction(function () use ($identity): User {
                $account = SocialAccount::with('user')
                    ->where('provider', 'github')
                    ->where('provider_user_id', $identity['id'])
                    ->first();

                if ($account) {
                    return $account->user;
                }

                $user = User::whereRaw('LOWER(email) = ?', [$identity['email']])->first();

                if (! $user) {
                    $user = User::create([
                        'name' => $identity['name'],
                        'email' => $identity['email'],
                        'password' => Str::random(64),
                    ]);
                    $user->forceFill(['email_verified_at' => now()])->save();
                    event(new Registered($user));
                }

                if (! $user->is_active) {
                    throw new RuntimeException('This account is unavailable.');
                }

                if ($user->socialAccounts()->where('provider', 'github')->exists()) {
                    throw new RuntimeException('A different GitHub account is already linked to this email.');
                }

                $user->socialAccounts()->create([
                    'provider' => 'github',
                    'provider_user_id' => $identity['id'],
                    'provider_email' => $identity['email'],
                ]);

                return $user;
            });

            if (! $user->is_active) {
                return redirect()->route('login')->withErrors(['email' => 'This account is unavailable.']);
            }

            Auth::login($user);
            $request->session()->regenerate();

            return redirect()->intended(route('discover'))->with('success', 'You are signed in with GitHub.');
        } catch (ConnectionException|RequestException|RuntimeException $exception) {
            report($exception);

            return redirect()->route('login')->withErrors([
                'email' => 'GitHub sign-in could not be completed. Check your account setup and try again.',
            ]);
        }
    }
}
