<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CalendarEvent extends Model
{
    protected $fillable = [
        'calendar_connection_id', 'movie_id', 'external_event_id', 'event_title',
        'starts_at', 'ends_at', 'sync_status', 'synced_at',
    ];

    protected $casts = [
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'synced_at' => 'datetime',
    ];

    public function connection(): BelongsTo
    {
        return $this->belongsTo(CalendarConnection::class, 'calendar_connection_id');
    }

    public function movie(): BelongsTo
    {
        return $this->belongsTo(Movie::class);
    }
}
