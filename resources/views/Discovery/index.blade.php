@extends('layouts.app')

@section('title', 'Discover Movies')

@section('app-content')
    @php
        $imageBase = rtrim(config('tmdb.image_base_url', 'https://image.tmdb.org/t/p'), '/');
        $featured = $popular->first();
    @endphp

    <section class="catalogue">
        <header class="catalogue-heading">
            <div>
                <p class="eyebrow">MOVIE DISCOVERY</p>
                <h1>Find your next movie</h1>
            </div>
        </header>

        @include('Integrations.discover-tools')

        @if(! $tmdbAvailable)
            <p class="service-message" role="status">
                TMDB is temporarily unavailable. Showing saved movies where possible.
            </p>
        @endif

        <form class="movie-search" method="GET" action="{{ route('movies.index') }}" role="search">
            <label class="search-query">
                <span>Search movies</span>
                <input type="search" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Search by title or description">
            </label>
            <label>
                <span>Genre</span>
                <select name="genre">
                    <option value="">All genres</option>
                    @foreach($genres as $genre)
                        <option value="{{ $genre->tmdb_id }}" {{ (string) ($filters['genre'] ?? '') === (string) $genre->tmdb_id ? 'selected' : '' }}>
                            {{ $genre->name }}
                        </option>
                    @endforeach
                </select>
            </label>
            <label>
                <span>Release year</span>
                <input type="number" name="year" min="1888" max="{{ date('Y') + 10 }}" value="{{ $filters['year'] ?? '' }}" placeholder="Any year">
            </label>
            <label>
                <span>Sort by</span>
                <select name="sort_by">
                    @foreach(['popularity.desc' => 'Popularity', 'vote_average.desc' => 'Rating', 'release_date.desc' => 'Release date', 'title.asc' => 'Title'] as $value => $label)
                        <option value="{{ $value }}" {{ ($filters['sort_by'] ?? 'popularity.desc') === $value ? 'selected' : '' }}>
                            {{ $label }}
                        </option>
                    @endforeach
                </select>
            </label>
            <div class="search-actions">
                <button type="submit">Search</button>
                <a class="reset-link" href="{{ route('discover') }}">Reset</a>
            </div>
        </form>

        @if($searchResults !== null)
            <section class="movie-section" aria-labelledby="results-heading">
                <div class="section-heading">
                    <h2 id="results-heading">Search results</h2>
                    <span>{{ $searchResults->count() }} movies</span>
                </div>
                @if($searchFailed)
                    <p class="service-message" role="status">Search is using the saved movie catalogue because TMDB could not be reached.</p>
                @endif
                <div class="movie-grid">
                    @forelse($searchResults as $movie)
                        @include('Discovery.partials.movie-card', ['movie' => $movie])
                    @empty
                        <p class="empty-state">No saved movies match these search options.</p>
                    @endforelse
                </div>
            </section>
        @else
            @if($featured)
                <section class="featured-movie" aria-labelledby="featured-title">
                    @if($featured->backdrop_path)
                        <img class="featured-image" src="{{ $imageBase }}/w1280{{ $featured->backdrop_path }}" alt="" fetchpriority="high">
                    @endif
                    <div class="featured-content">
                        <p class="eyebrow">POPULAR ON TMDB</p>
                        <h2 id="featured-title">{{ $featured->title }}</h2>
                        <p class="featured-overview">{{ $featured->overview }}</p>
                        <p class="movie-meta">
                            {{ $featured->release_date?->format('Y') ?? 'Release date unavailable' }}
                            <span aria-hidden="true">·</span>
                            ★ {{ number_format((float) $featured->vote_average, 1) }}
                        </p>
                        <a class="btn" href="{{ route('movies.show', $featured->tmdb_id) }}">View details</a>
                    </div>
                </section>
            @endif

            <section class="movie-section" id="popular" aria-labelledby="popular-heading">
                <div class="section-heading">
                    <h2 id="popular-heading">Popular movies</h2>
                    <a href="{{ route('movies.index') }}">Browse all movies</a>
                </div>
                <div class="movie-grid">
                    @forelse($popular as $movie)
                        @include('Discovery.partials.movie-card', ['movie' => $movie])
                    @empty
                        <p class="empty-state">Popular movies will appear when TMDB is configured.</p>
                    @endforelse
                </div>
            </section>

            <section class="movie-section" id="upcoming" aria-labelledby="upcoming-heading">
                <div class="section-heading">
                    <h2 id="upcoming-heading">Upcoming movies</h2>
                </div>
                <div class="movie-grid">
                    @forelse($upcoming as $movie)
                        @include('Discovery.partials.movie-card', ['movie' => $movie])
                    @empty
                        <p class="empty-state">No upcoming movies are available right now.</p>
                    @endforelse
                </div>
            </section>
        @endif
    </section>

    <style>
        .catalogue { display: grid; gap: 30px; }
        .catalogue-heading { display: flex; align-items: end; justify-content: space-between; }
        .catalogue h1, .catalogue h2, .catalogue h3, .movie-detail h1, .movie-detail h2 { margin: 0; line-height: 1.2; }
        .catalogue h1 { font-size: clamp(28px, 4vw, 42px); }
        .eyebrow { margin: 0 0 8px; color: #e0ae55; font-size: 12px; font-weight: 700; letter-spacing: .12em; }
        .service-message { border: 1px solid #4a5664; border-radius: 8px; padding: 12px 14px; color: #d5dbe2; background: #191f27; }
        .movie-search { display: grid; grid-template-columns: 2fr 1fr 1fr 1.2fr auto; gap: 12px; align-items: end; padding: 16px; background: #191f27; border: 1px solid #2d3541; border-radius: 10px; }
        .movie-search label { display: grid; gap: 6px; color: #a8b0bb; font-size: 13px; }
        .movie-search input, .movie-search select { min-width: 0; }
        .search-actions { display: flex; align-items: center; gap: 12px; }
        .reset-link, .section-heading a { color: #e0ae55; text-decoration: none; }
        .featured-movie { min-height: 340px; position: relative; overflow: hidden; display: flex; align-items: end; border: 1px solid #2d3541; border-radius: 12px; background: #191f27; }
        .featured-image { position: absolute; inset: 0; width: 100%; height: 100%; object-fit: cover; }
        .featured-movie::after { content: ''; position: absolute; inset: 0; background: linear-gradient(90deg, rgba(17,21,27,.97) 0%, rgba(17,21,27,.78) 48%, rgba(17,21,27,.08) 100%); }
        .featured-content { position: relative; z-index: 1; max-width: 590px; padding: 34px; }
        .featured-content h2 { font-size: clamp(30px, 4vw, 46px); margin-bottom: 12px; }
        .featured-overview { color: #d0d5dc; max-width: 520px; }
        .movie-meta { color: #c1c8d0; display: flex; gap: 10px; align-items: center; margin: 16px 0 22px; }
        .movie-section { display: grid; gap: 16px; }
        .section-heading { display: flex; align-items: baseline; justify-content: space-between; gap: 12px; }
        .section-heading h2 { font-size: 23px; }
        .section-heading span { color: #a8b0bb; font-size: 14px; }
        .movie-grid { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 18px; }
        .movie-card { min-width: 0; display: block; overflow: hidden; border: 1px solid #2d3541; border-radius: 9px; background: #191f27; text-decoration: none; transition: border-color .15s ease; }
        .movie-card:hover { border-color: #e0ae55; }
        .movie-poster { width: 100%; aspect-ratio: 2 / 3; object-fit: cover; display: block; background: #202832; }
        .poster-placeholder { aspect-ratio: 2 / 3; display: grid; place-items: center; background: #202832; color: #a8b0bb; text-align: center; padding: 16px; }
        .movie-card-info { padding: 11px 12px 13px; }
        .movie-card-info h3 { font-size: 15px; margin: 0 0 6px; }
        .movie-card-info p { color: #a8b0bb; font-size: 13px; margin: 0; }
        .empty-state { color: #a8b0bb; }
        @media (max-width: 900px) {
            .movie-search { grid-template-columns: repeat(2, minmax(0, 1fr)); }
            .search-query { grid-column: 1 / -1; }
            .movie-grid { grid-template-columns: repeat(3, minmax(0, 1fr)); }
        }
        @media (max-width: 600px) {
            .movie-search { grid-template-columns: 1fr; }
            .search-query { grid-column: auto; }
            .movie-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 12px; }
            .featured-movie { min-height: 300px; }
            .featured-content { padding: 22px; }
        }
    </style>
@endsection
