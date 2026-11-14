<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use RuntimeException;

class GitHubOAuthService
{
    public function authorizationUrl(string $state): string
    {
        return 'https://github.com/login/oauth/authorize?' . http_build_query([
            'client_id' => $this->clientId(),
            'redirect_uri' => $this->redirectUri(),
            'scope' => 'read:user user:email',
            'state' => $state,
        ]);
    }

    public function identity(string $code): array
    {
        $tokenResponse = Http::asForm()
            ->acceptJson()
            ->timeout(15)
            ->post('https://github.com/login/oauth/access_token', [
                'client_id' => $this->clientId(),
                'client_secret' => $this->clientSecret(),
                'code' => $code,
                'redirect_uri' => $this->redirectUri(),
            ])
            ->throw()
            ->json();

        $accessToken = is_array($tokenResponse) ? ($tokenResponse['access_token'] ?? null) : null;
        if (! is_string($accessToken) || $accessToken === '') {
            throw new RuntimeException('GitHub did not return an access token.');
        }

        $api = Http::withToken($accessToken)
            ->acceptJson()
            ->withHeaders(['User-Agent' => (string) config('app.name', 'Movie Discovery')])
            ->timeout(15);

        $profile = $api->get('https://api.github.com/user')->throw()->json();
        $emails = $api->get('https://api.github.com/user/emails')->throw()->json();

        if (! is_array($profile) || ! isset($profile['id']) || ! is_array($emails)) {
            throw new RuntimeException('GitHub returned an incomplete account profile.');
        }

        $verifiedEmails = collect($emails)->filter(
            fn ($item) => is_array($item)
                && ($item['verified'] ?? false) === true
                && filter_var($item['email'] ?? null, FILTER_VALIDATE_EMAIL)
        );
        $email = $verifiedEmails->first(fn ($item) => ($item['primary'] ?? false) === true)
            ?? $verifiedEmails->first();

        if (! is_array($email)) {
            throw new RuntimeException('A verified email address is required to sign in with GitHub.');
        }

        $name = trim((string) ($profile['name'] ?? ''));
        if ($name === '') {
            $name = trim((string) ($profile['login'] ?? ''));
        }

        if ($name === '') {
            $name = 'Movie Fan';
        }

        return [
            'id' => (string) $profile['id'],
            'name' => mb_substr($name, 0, 100),
            'email' => strtolower(trim((string) $email['email'])),
        ];
    }

    private function clientId(): string
    {
        $value = trim((string) config('services.github_oauth.client_id'));
        if ($value === '') {
            throw new RuntimeException('GitHub OAuth is not configured.');
        }

        return $value;
    }

    private function clientSecret(): string
    {
        $value = trim((string) config('services.github_oauth.client_secret'));
        if ($value === '') {
            throw new RuntimeException('GitHub OAuth is not configured.');
        }

        return $value;
    }

    private function redirectUri(): string
    {
        return trim((string) config('services.github_oauth.redirect'))
            ?: route('social.github.callback');
    }
}
