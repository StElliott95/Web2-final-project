<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DiscussionRoom extends Model
{
    protected $fillable = ['movie_id', 'community_id', 'created_by', 'title', 'is_active'];

    public function community(): BelongsTo { return $this->belongsTo(Community::class); }

    protected $casts = ['is_active' => 'boolean'];

    public function movie(): BelongsTo
    {
        return $this->belongsTo(Movie::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function members(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'discussion_room_members', 'room_id', 'user_id')
            ->withPivot(['joined_at', 'left_at'])
            ->withTimestamps();
    }

    public function messages(): HasMany
    {
        return $this->hasMany(DiscussionMessage::class, 'room_id');
    }
}
