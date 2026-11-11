<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use RuntimeException;

class TmdbService
{
    private function request(string $path, array $query = []): array
    {
        $token = trim((string) config('tmdb.read_access_token'));

        if ($token === '') {
            throw new RuntimeException('TMDB_READ_ACCESS_TOKEN is not configured.');
        }

        $baseUrl = rtrim((string) config('tmdb.base_url', 'https://api.themoviedb.org/3'), '/');
        $response = Http::withToken($token)
            ->acceptJson()
            ->timeout(10)
            ->get($baseUrl . '/' . ltrim($path, '/'), $query);

        $response->throw();

        $payload = $response->json();

        return is_array($payload) ? $payload : [];
    }

    public function popularMovies(): array
    {
        return $this->request('movie/popular', ['language' => 'en-US']);
    }

    public function upcomingMovies(): array
    {
        return $this->request('movie/upcoming', ['language' => 'en-US']);
    }

    public function movieGenres(): array
    {
        return $this->request('genre/movie/list', ['language' => 'en-US']);
    }

    public function searchMovies(string $query): array
    {
        return $this->request('search/movie', [
            'query' => $query,
            'include_adult' => false,
            'language' => 'en-US',
        ]);
    }

    public function discoverMovies(array $filters): array
    {
        return $this->request('discover/movie', array_merge([
            'include_adult' => false,
            'language' => 'en-US',
        ], $filters));
    }

    public function movieDetails(int $tmdbId): array
    {
        return $this->request('movie/' . $tmdbId, [
            'append_to_response' => 'videos',
            'language' => 'en-US',
        ]);
    }

    public function movieCredits(int $tmdbId): array
    {
        return $this->request('movie/' . $tmdbId . '/credits', [
            'language' => 'en-US',
        ]);
    }
}
