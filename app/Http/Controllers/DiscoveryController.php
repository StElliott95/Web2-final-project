<?php

namespace App\Http\Controllers;

use App\Models\Genre;
use App\Models\Movie;
use App\Services\MovieCatalogSync;
use App\Services\TmdbService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;
use RuntimeException;

class DiscoveryController extends Controller
{
    private TmdbService $tmdb;
    private MovieCatalogSync $catalog;

    public function __construct(TmdbService $tmdb, MovieCatalogSync $catalog)
    {
        $this->tmdb = $tmdb;
        $this->catalog = $catalog;
    }

    public function index(Request $request): View
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'genre' => ['nullable', 'integer', 'min:1'],
            'year' => ['nullable', 'integer', 'min:1888', 'max:' . (date('Y') + 10)],
            'sort_by' => ['nullable', 'in:popularity.desc,vote_average.desc,release_date.desc,title.asc'],
        ]);

        $tmdbAvailable = true;
        $searchFailed = false;

        try {
            $popularData = Cache::remember(
                'tmdb:popular',
                now()->addMinutes(30),
                fn () => $this->tmdb->popularMovies()
            );
            $upcomingData = Cache::remember(
                'tmdb:upcoming',
                now()->addMinutes(60),
                fn () => $this->tmdb->upcomingMovies()
            );
            $genreData = Cache::remember(
                'tmdb:movie-genres',
                now()->addDay(),
                fn () => $this->tmdb->movieGenres()
            );

            $this->catalog->syncGenreList($genreData['genres'] ?? []);
            $popular = $this->catalog->syncMovies($this->nonAdult($popularData['results'] ?? []));
            $upcoming = $this->catalog->syncMovies($this->nonAdult($upcomingData['results'] ?? []));
        } catch (ConnectionException|RequestException|RuntimeException $exception) {
            report($exception);
            $tmdbAvailable = false;
            $popular = $this->localCatalogue(['sort_by' => 'popularity.desc'])->take(8);
            $upcoming = Movie::with('genres')
                ->whereDate('release_date', '>=', now()->toDateString())
                ->orderBy('release_date')
                ->limit(8)
                ->get();
        }

        $genres = Genre::orderBy('name')->get();
        $hasFilters = collect($filters)->contains(fn ($value) => $value !== null && $value !== '');

        $searchResults = null;

        if ($hasFilters) {
            try {
                $items = $this->fetchFilteredMovies($filters);
                $searchResults = $this->catalog->syncMovies($this->nonAdult($items));
            } catch (ConnectionException|RequestException|RuntimeException $exception) {
                report($exception);
                $tmdbAvailable = false;
                $searchFailed = true;
                $searchResults = $this->localCatalogue($filters)->get();
            }
        }

        return view('Discovery.index', compact(
            'filters',
            'genres',
            'popular',
            'upcoming',
            'searchResults',
            'tmdbAvailable',
            'searchFailed'
        ));
    }

    public function show(int $tmdbId): View
    {
        $tmdbAvailable = true;

        try {
            $details = Cache::remember(
                'tmdb:movie:' . $tmdbId,
                now()->addHours(12),
                fn () => $this->tmdb->movieDetails($tmdbId)
            );
            $credits = Cache::remember(
                'tmdb:credits:' . $tmdbId,
                now()->addHours(12),
                fn () => $this->tmdb->movieCredits($tmdbId)
            );
            $movie = $this->catalog->syncDetails($details, $credits);
        } catch (ConnectionException|RequestException|RuntimeException $exception) {
            report($exception);
            $tmdbAvailable = false;
            $movie = Movie::with(['genres', 'credits.person'])
                ->where('tmdb_id', $tmdbId)
                ->firstOrFail();
            $details = [];
        }

        return view('Movies.show', compact('movie', 'details', 'tmdbAvailable'));
    }

    private function fetchFilteredMovies(array $filters): array
    {
        $query = trim((string) ($filters['q'] ?? ''));

        if ($query !== '') {
            $cacheKey = 'tmdb:search:' . sha1(json_encode($filters));
            $payload = Cache::remember(
                $cacheKey,
                now()->addMinutes(10),
                fn () => $this->tmdb->searchMovies($query)
            );

            return $this->filterSearchResults($payload['results'] ?? [], $filters);
        }

        $parameters = array_filter([
            'with_genres' => $filters['genre'] ?? null,
            'primary_release_year' => $filters['year'] ?? null,
            'sort_by' => $filters['sort_by'] ?? 'popularity.desc',
        ], fn ($value) => $value !== null && $value !== '');

        $cacheKey = 'tmdb:discover:' . sha1(json_encode($parameters));
        $payload = Cache::remember(
            $cacheKey,
            now()->addMinutes(30),
            fn () => $this->tmdb->discoverMovies($parameters)
        );

        return $payload['results'] ?? [];
    }

    private function filterSearchResults(array $results, array $filters): array
    {
        $items = collect($this->nonAdult($results));

        if (! empty($filters['genre'])) {
            $genreId = (int) $filters['genre'];
            $items = $items->filter(
                fn ($item) => in_array($genreId, array_map('intval', $item['genre_ids'] ?? []), true)
            );
        }

        if (! empty($filters['year'])) {
            $year = (string) $filters['year'];
            $items = $items->filter(
                fn ($item) => str_starts_with((string) ($item['release_date'] ?? ''), $year)
            );
        }

        $items = match ($filters['sort_by'] ?? 'popularity.desc') {
            'vote_average.desc' => $items->sortByDesc('vote_average'),
            'release_date.desc' => $items->sortByDesc('release_date'),
            'title.asc' => $items->sortBy('title'),
            default => $items->sortByDesc('popularity'),
        };

        return $items->values()->all();
    }

    private function nonAdult(array $items): array
    {
        return array_values(array_filter($items, fn ($item) => ! ($item['adult'] ?? false)));
    }

    private function localCatalogue(array $filters): Collection
    {
        $query = Movie::with('genres');

        if (! empty($filters['q'])) {
            $term = trim((string) $filters['q']);
            $query->where(function (Builder $builder) use ($term) {
                $builder->where('title', 'like', '%' . $term . '%')
                    ->orWhere('original_title', 'like', '%' . $term . '%')
                    ->orWhere('overview', 'like', '%' . $term . '%');
            });
        }

        if (! empty($filters['genre'])) {
            $genreId = (int) $filters['genre'];
            $query->whereHas('genres', fn (Builder $builder) => $builder->where('tmdb_id', $genreId));
        }

        if (! empty($filters['year'])) {
            $query->whereYear('release_date', (int) $filters['year']);
        }

        switch ($filters['sort_by'] ?? 'popularity.desc') {
            case 'vote_average.desc':
                $query->orderByDesc('vote_average');
                break;
            case 'release_date.desc':
                $query->orderByDesc('release_date');
                break;
            case 'title.asc':
                $query->orderBy('title');
                break;
            default:
                $query->orderByDesc('popularity');
        }

        return $query->limit(40)->get();
    }
}
