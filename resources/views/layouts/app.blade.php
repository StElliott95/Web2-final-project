<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="color-scheme" content="dark">
    <title>@yield('title', 'Movie Discovery')</title>
    <style>
        :root {
            color-scheme: dark;
            --canvas: #11151b;
            --surface: #191f27;
            --surface-raised: #202832;
            --text: #f4f3ef;
            --muted: #a8b0bb;
            --accent: #e0ae55;
            --border: #2d3541;
            --danger: #d76565;
            --success: #65bf91;
        }
        * { box-sizing: border-box; }
        body { margin: 0; background: var(--canvas); color: var(--text); font: 16px/1.5 Arial, sans-serif; }
        a { color: inherit; }
        a:focus-visible, button:focus-visible, input:focus-visible, select:focus-visible, textarea:focus-visible {
            outline: 3px solid var(--accent); outline-offset: 2px;
        }
        .site-header { min-height: 64px; border-bottom: 1px solid var(--border); display: flex; align-items: center; gap: 32px; padding: 0 max(24px, calc((100vw - 1200px) / 2)); }
        .brand { color: var(--text); font-weight: 700; font-size: 17px; letter-spacing: .02em; text-decoration: none; white-space: nowrap; }
        .site-nav { display: flex; align-items: center; gap: 26px; flex: 1; }
        .site-nav a, .header-account { color: var(--muted); text-decoration: none; }
        .site-nav a:hover, .header-account:hover { color: var(--text); }
        .header-account { white-space: nowrap; }
        .page-shell { width: min(1200px, calc(100% - 40px)); margin: 32px auto 64px; min-height: 65vh; }
        .flash { padding: 12px 15px; border: 1px solid var(--border); border-radius: 8px; margin-bottom: 16px; }
        .success { color: var(--success); }
        .error { color: #ffaaaa; }
        .danger { background: #963e43 !important; }
        .btn, button { display: inline-flex; align-items: center; justify-content: center; gap: 8px; background: var(--accent); color: #171717; border: 1px solid transparent; border-radius: 7px; padding: 9px 15px; font-weight: 700; cursor: pointer; text-decoration: none; }
        .btn:hover, button:hover { filter: brightness(1.05); }
        input, select, textarea { width: 100%; background: #11151b; color: var(--text); border: 1px solid var(--border); border-radius: 7px; padding: 10px 12px; font: inherit; }
        input::placeholder, textarea::placeholder { color: #8b95a2; }
        form.inline { display: inline; }
        table { width: 100%; border-collapse: collapse; margin: 16px 0; background: var(--surface); }
        th { background: #233247; color: white; text-align: left; }
        th, td { padding: 11px 12px; border: 1px solid var(--border); }
        .content { background: transparent; max-width: none; margin: 0; padding: 0; border-radius: 0; }
        .site-footer { border-top: 1px solid var(--border); color: var(--muted); padding: 20px 24px; text-align: center; font-size: 13px; }
        .site-footer a { color: var(--muted); }
        @media (max-width: 680px) {
            .site-header { flex-wrap: wrap; gap: 8px 18px; padding: 12px 20px; }
            .site-nav { order: 3; width: 100%; gap: 20px; overflow-x: auto; padding: 6px 0; }
            .header-account { margin-left: auto; }
            .page-shell { width: min(100% - 28px, 1200px); margin-top: 22px; }
        }
    </style>
    @yield('head')
</head>
<body>
    <header class="site-header">
        <a class="brand" href="{{ route('discover') }}">MOVIE DISCOVERY</a>
        <nav class="site-nav" aria-label="Main navigation">
            <a href="{{ route('discover') }}">Discover</a>
            <a href="{{ route('discover') }}#popular">Popular</a>
            <a href="{{ route('discover') }}#upcoming">Upcoming</a>
        </nav>
        @auth
            <span class="header-account">{{ auth()->user()->name }}</span>
            @if(auth()->user()->role === 'admin' && \Illuminate\Support\Facades\Route::has('admin-reviews'))
                <a class="header-account" href="{{ route('admin-reviews') }}">Moderation</a>
            @endif
        @else
            @if(\Illuminate\Support\Facades\Route::has('login'))
                <a class="header-account" href="{{ route('login') }}">Sign in</a>
            @endif
        @endauth
    </header>

    <main class="page-shell">
        @if(session('success'))
            <p class="flash success" role="status">{{ session('success') }}</p>
        @endif
        @if(session('error'))
            <p class="flash error" role="alert">{{ session('error') }}</p>
        @endif
        @foreach($errors->all() as $error)
            <p class="flash error" role="alert">{{ $error }}</p>
        @endforeach
        <div class="content">
            @yield('app-content')
        </div>
    </main>

    <footer class="site-footer">
        Movie data and images provided by
        <a href="https://www.themoviedb.org/" rel="noopener noreferrer" target="_blank">TMDB</a>.
        This site is not endorsed or certified by TMDB.
    </footer>
</body>
</html>
