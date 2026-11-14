@if(config('services.github_oauth.client_id') && config('services.github_oauth.client_secret'))
    <div class="auth-social-separator" aria-hidden="true">or</div>
    <a class="btn auth-social-button" href="{{ route('social.github.redirect') }}">Continue with GitHub</a>
@endif
