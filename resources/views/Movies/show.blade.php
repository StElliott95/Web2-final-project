@extends('layouts.app')

@section('title', $movie->title)

@section('app-content')
    @php
        $imageBase = rtrim(config('tmdb.image_base_url', 'https://image.tmdb.org/t/p'), '/');
        $cast = $movie->credits->where('credit_type', 'cast')->sortBy('cast_order')->take(12);
        $director = $movie->credits->first(fn ($credit) => $credit->job === 'Director');
        $trailer = collect($details['videos']['results'] ?? [])->first(
            fn ($video) => ($video['site'] ?? '') === 'YouTube' && ($video['type'] ?? '') === 'Trailer'
        );
    @endphp

    <article class="movie-detail">
        @if(! $tmdbAvailable)
            <p class="service-message" role="status">TMDB is unavailable. Showing saved movie details.</p>
        @endif

        @if($movie->backdrop_path)
            <div class="detail-backdrop">
                <img src="{{ $imageBase }}/w1280{{ $movie->backdrop_path }}" alt="" fetchpriority="high">
            </div>
        @endif

        <div class="detail-main">
            @if($movie->poster_path)
                <img class="detail-poster" src="{{ $imageBase }}/w500{{ $movie->poster_path }}" alt="Poster for {{ $movie->title }}">
            @else
                <div class="detail-poster poster-placeholder">Poster unavailable</div>
            @endif

            <div class="detail-copy">
                <p class="eyebrow">TMDB MOVIE DETAILS</p>
                <h1>{{ $movie->title }}</h1>
                @if($movie->original_title && $movie->original_title !== $movie->title)
                    <p class="muted">{{ $movie->original_title }}</p>
                @endif
                <p class="movie-meta">
                    {{ $movie->release_date?->format('F j, Y') ?? 'Release date unavailable' }}
                    <span aria-hidden="true">·</span>
                    {{ $movie->runtime ? $movie->runtime . ' min' : 'Runtime unavailable' }}
                    <span aria-hidden="true">·</span>
                    ★ {{ number_format((float) $movie->vote_average, 1) }}
                </p>
                <div class="genre-list">
                    @forelse($movie->genres as $genre)
                        <span>{{ $genre->name }}</span>
                    @empty
                        <span>Genres unavailable</span>
                    @endforelse
                </div>
                <div class="movie-actions">
                    @if($movie->release_date)
                        <a class="btn" href="{{ route('integrations.calendar-export', $movie->id) }}">Add release to calendar</a>
                    @endif
                    @auth
                        <a class="btn" href="{{ route('integrations.discussion-room', $movie->id) }}">Join movie discussion</a>
                    @endauth
                </div>
                <h2>Overview</h2>
                <p>{{ $movie->overview ?: 'No overview is available.' }}</p>
                @if($director)
                    <p><strong>Director:</strong> {{ $director->person->name }}</p>
                @endif
                @if($trailer)
                    <a class="btn" href="https://www.youtube.com/watch?v={{ urlencode($trailer['key']) }}" target="_blank" rel="noopener noreferrer">
                        Watch trailer
                    </a>
                @else
                    <p class="muted">No trailer is available from TMDB.</p>
                @endif
            </div>
        </div>

        <section class="cast-section" aria-labelledby="cast-heading">
            <h2 id="cast-heading">Cast</h2>
            @if($cast->isNotEmpty())
                <div class="cast-grid">
                    @foreach($cast as $credit)
                        <article class="cast-card">
                            @if($credit->person->profile_path)
                                <img src="{{ $imageBase }}/w185{{ $credit->person->profile_path }}" alt="" loading="lazy">
                            @else
                                <div class="cast-placeholder" aria-hidden="true"></div>
                            @endif
                            <div>
                                <strong>{{ $credit->person->name }}</strong>
                                @if($credit->character_name)
                                    <span>{{ $credit->character_name }}</span>
                                @endif
                            </div>
                        </article>
                    @endforeach
                </div>
            @else
                <p class="muted">Cast information is not available.</p>
            @endif
        </section>
    </article>

    <style>
        .movie-detail { display: grid; gap: 30px; }
        .service-message { border: 1px solid #4a5664; border-radius: 8px; padding: 12px 14px; color: #d5dbe2; background: #191f27; }
        .detail-backdrop { height: 250px; overflow: hidden; border: 1px solid #2d3541; border-radius: 10px; background: #191f27; }
        .detail-backdrop img { width: 100%; height: 100%; object-fit: cover; }
        .detail-main { display: grid; grid-template-columns: minmax(190px, 280px) 1fr; gap: 28px; align-items: start; }
        .detail-poster { width: 100%; aspect-ratio: 2 / 3; object-fit: cover; border: 1px solid #2d3541; border-radius: 9px; background: #202832; }
        .detail-copy h1 { font-size: clamp(30px, 4vw, 44px); margin: 0 0 12px; }
        .detail-copy h2, .cast-section h2 { font-size: 22px; margin: 24px 0 10px; }
        .muted { color: #a8b0bb; }
        .movie-actions { display: flex; flex-wrap: wrap; gap: 10px; margin: 16px 0; }
        .genre-list { display: flex; flex-wrap: wrap; gap: 8px; margin: 14px 0; }
        .genre-list span { color: #f4f3ef; background: #202832; border: 1px solid #2d3541; border-radius: 99px; padding: 4px 10px; font-size: 13px; }
        .cast-grid { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 12px; }
        .cast-card { display: flex; gap: 10px; align-items: center; min-width: 0; background: #191f27; border: 1px solid #2d3541; border-radius: 8px; padding: 8px; }
        .cast-card img, .cast-placeholder { width: 48px; height: 64px; object-fit: cover; border-radius: 5px; flex: 0 0 auto; background: #202832; }
        .cast-card div:last-child { display: grid; gap: 3px; min-width: 0; }
        .cast-card strong, .cast-card span { overflow-wrap: anywhere; }
        .cast-card span { color: #a8b0bb; font-size: 12px; }
        @media (max-width: 760px) {
            .detail-backdrop { height: 180px; }
            .detail-main { grid-template-columns: minmax(110px, 32%) 1fr; gap: 16px; }
            .cast-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        }
        @media (max-width: 520px) {
            .detail-main { grid-template-columns: 1fr; }
            .detail-poster { width: min(220px, 70%); }
            .detail-copy h1 { font-size: 31px; }
        }
    </style>
@endsection
