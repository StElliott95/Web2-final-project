<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class MovieList extends Model {
    protected $attributes = ['is_public' => false];
    protected $fillable = ['user_id','title','description','is_public'];
    protected function casts(): array { return ['is_public'=>'boolean']; }
    public function user() { return $this->belongsTo(User::class); }
    public function movies() { return $this->belongsToMany(Movie::class, 'movie_list_movie')->withTimestamps(); }
}

