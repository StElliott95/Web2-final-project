@php
    $imageBase = rtrim(config('tmdb.image_base_url', 'https://image.tmdb.org/t/p'), '/');
@endphp
<a class="movie-card" href="{{ route('movies.show', $movie->tmdb_id) }}">
    @if($movie->poster_path)
        <img class="movie-poster" src="{{ $imageBase }}/w500{{ $movie->poster_path }}" alt="Poster for {{ $movie->title }}" loading="lazy">
    @else
        <span class="poster-placeholder">Poster unavailable</span>
    @endif
    <div class="movie-card-info">
        <h3>{{ $movie->title }}</h3>
        <p>
            {{ $movie->release_date?->format('Y') ?? 'Release date TBA' }}
            <span aria-hidden="true">·</span>
            ★ {{ number_format((float) $movie->vote_average, 1) }}
        </p>
    </div>
</a>
