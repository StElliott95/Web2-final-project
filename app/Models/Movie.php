<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Movie extends Model
{
    protected $fillable = [
        'tmdb_id', 'title', 'original_title', 'overview', 'poster_path', 'backdrop_path',
        'release_date', 'runtime', 'original_language', 'vote_average', 'vote_count',
        'popularity', 'adult', 'last_synced_at',
    ];

    protected $casts = [
        'release_date' => 'date',
        'last_synced_at' => 'datetime',
        'adult' => 'boolean',
        'vote_average' => 'decimal:2',
    ];

    public function genres(): BelongsToMany
    {
        return $this->belongsToMany(Genre::class, 'movie_genre')->withTimestamps();
    }

    public function credits(): HasMany
    {
        return $this->hasMany(MovieCredit::class);
    }

    public function people(): BelongsToMany
    {
        return $this->belongsToMany(Person::class, 'movie_credits')
            ->withPivot(['credit_type', 'department', 'job', 'character_name', 'cast_order'])
            ->withTimestamps();
    }

    public function calendarEvents(): HasMany
    {
        return $this->hasMany(CalendarEvent::class);
    }

    public function discussionRooms(): HasMany
    {
        return $this->hasMany(DiscussionRoom::class);
    }
}
