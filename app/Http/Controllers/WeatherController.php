<?php

namespace App\Http\Controllers;

use App\Models\Movie;
use App\Services\WeatherApiService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use RuntimeException;

class WeatherController extends Controller
{
    public function __invoke(Request $request, WeatherApiService $weather): JsonResponse
    {
        $coordinates = $request->validate([
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
        ]);

        try {
            $current = $weather->current(
                (float) $coordinates['latitude'],
                (float) $coordinates['longitude']
            );
        } catch (ConnectionException|RequestException|RuntimeException $exception) {
            report($exception);

            return response()->json([
                'message' => 'Weather suggestions are unavailable right now.',
            ], 503);
        }

        $condition = mb_strtolower($current['condition']);
        $genre = match (true) {
            preg_match('/rain|snow|storm|cold/', $condition) === 1 => 'Drama',
            preg_match('/clear|sun|hot/', $condition) === 1 => 'Adventure',
            preg_match('/cloud|fog|mist/', $condition) === 1 => 'Mystery',
            default => 'Comedy',
        };

        $movies = Movie::query()
            ->with('genres')
            ->whereHas('genres', fn (Builder $query) => $query->whereRaw('LOWER(name) = ?', [mb_strtolower($genre)]))
            ->orderByDesc('popularity')
            ->limit(8)
            ->get(['id', 'tmdb_id', 'title', 'poster_path', 'release_date', 'vote_average']);

        return response()->json([
            'weather' => $current,
            'suggested_genre' => $genre,
            'movies' => $movies->map(fn (Movie $movie) => [
                'title' => $movie->title,
                'url' => route('movies.show', $movie->tmdb_id),
                'year' => $movie->release_date?->format('Y'),
                'vote_average' => (float) $movie->vote_average,
            ])->values(),
        ]);
    }
}
