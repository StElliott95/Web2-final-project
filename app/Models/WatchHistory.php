<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WatchHistory extends Model

{

    protected $fillable = ['user_id', 'movie_id', 'watched_at'];

    protected $casts = [

        'watched_at' => 'datetime',

    ];

    public function getUser()

    {

        return $this->belongsTo(User::class, 'user_id', 'id');

    }

    public function getMovie()

    {

        return $this->belongsTo(Movie::class, 'movie_id', 'id');

    }

}
 