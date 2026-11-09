<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Person extends Model
{
    protected $fillable = ['tmdb_person_id', 'name', 'profile_path', 'known_for_department'];

    public function movieCredits(): HasMany
    {
        return $this->hasMany(MovieCredit::class);
    }

    public function movies(): BelongsToMany
    {
        return $this->belongsToMany(Movie::class, 'movie_credits')
            ->withPivot(['credit_type', 'department', 'job', 'character_name', 'cast_order'])
            ->withTimestamps();
    }
}
