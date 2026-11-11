<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class WatchlistItem extends Model
{
   protected $fillable = ['user_id', 'movie_id', 'is_watched', 'watched_at'];
   protected $casts = [
       'is_watched' => 'boolean',
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