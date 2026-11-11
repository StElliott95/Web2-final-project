<?php

namespace App\Services;

use App\Models\Genre;
use App\Models\Movie;
use App\Models\Person;
use Illuminate\Support\Facades\DB;

class MovieCatalogSync
{
    public function syncGenreList(array $genres): void
    {
        foreach ($genres as $genre) {
            if (empty($genre['id']) || empty($genre['name'])) {
                continue;
            }

            Genre::updateOrCreate(
                ['tmdb_id' => (int) $genre['id']],
                ['name' => $genre['name']]
            );
        }
    }

    public function syncMovies(array $items): array
    {
        $movies = [];

        foreach ($items as $item) {
            if (! is_array($item) || empty($item['id'])) {
                continue;
            }

            $movies[] = $this->syncMovie($item);
        }

        return $movies;
    }

    public function syncMovie(array $data): Movie
    {
        $title = trim((string) ($data['title'] ?? ''));

        if ($title === '' || empty($data['id'])) {
            throw new \InvalidArgumentException('TMDB movie data must include an id and title.');
        }

        $attributes = [
            'title' => $title,
            'last_synced_at' => now(),
        ];

        $fieldMap = [
            'original_title' => 'original_title',
            'overview' => 'overview',
            'poster_path' => 'poster_path',
            'backdrop_path' => 'backdrop_path',
            'release_date' => 'release_date',
            'runtime' => 'runtime',
            'original_language' => 'original_language',
            'vote_average' => 'vote_average',
            'vote_count' => 'vote_count',
            'popularity' => 'popularity',
            'adult' => 'adult',
        ];

        foreach ($fieldMap as $source => $target) {
            if (array_key_exists($source, $data)) {
                $value = $data[$source];
                $attributes[$target] = $source === 'release_date' && $value === '' ? null : $value;
            }
        }

        $movie = Movie::updateOrCreate(
            ['tmdb_id' => (int) $data['id']],
            $attributes
        );

        if (isset($data['genres']) && is_array($data['genres'])) {
            $genreIds = [];

            foreach ($data['genres'] as $genre) {
                if (empty($genre['id']) || empty($genre['name'])) {
                    continue;
                }

                $record = Genre::updateOrCreate(
                    ['tmdb_id' => (int) $genre['id']],
                    ['name' => $genre['name']]
                );
                $genreIds[] = $record->id;
            }

            $movie->genres()->sync($genreIds);
        } elseif (isset($data['genre_ids']) && is_array($data['genre_ids'])) {
            $genreIds = Genre::whereIn('tmdb_id', array_map('intval', $data['genre_ids']))
                ->pluck('id')
                ->all();

            if ($genreIds !== []) {
                $movie->genres()->sync($genreIds);
            }
        }

        return $movie;
    }

    public function syncDetails(array $details, array $credits): Movie
    {
        return DB::transaction(function () use ($details, $credits) {
            $movie = $this->syncMovie($details);

            if (array_key_exists('cast', $credits) || array_key_exists('crew', $credits)) {
                $movie->credits()->delete();

                foreach (array_slice($credits['cast'] ?? [], 0, 20) as $personData) {
                    $this->storeCredit($movie, $personData, 'cast');
                }

                foreach ($credits['crew'] ?? [] as $personData) {
                    if (($personData['job'] ?? '') === 'Director') {
                        $this->storeCredit($movie, $personData, 'crew');
                    }
                }
            }

            return $movie->fresh(['genres', 'credits.person']);
        });
    }

    private function storeCredit(Movie $movie, array $personData, string $creditType): void
    {
        if (empty($personData['id']) || empty($personData['name'])) {
            return;
        }

        $person = Person::updateOrCreate(
            ['tmdb_person_id' => (int) $personData['id']],
            [
                'name' => $personData['name'],
                'profile_path' => $personData['profile_path'] ?? null,
                'known_for_department' => $personData['known_for_department']
                    ?? $personData['department']
                    ?? null,
            ]
        );

        $movie->credits()->create([
            'person_id' => $person->id,
            'credit_type' => $creditType,
            'department' => $personData['department'] ?? null,
            'job' => $personData['job'] ?? null,
            'character_name' => $personData['character'] ?? null,
            'cast_order' => isset($personData['order']) ? (int) $personData['order'] : null,
        ]);
    }
}
