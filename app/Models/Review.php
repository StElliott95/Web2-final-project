<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Review extends Model
{
   protected $fillable = ['user_id', 'movie_id', 'rating', 'body', 'is_hidden', 'sentiment_label', 'sentiment_score', 'sentiment_analyzed_at'];
   protected $casts = ['sentiment_score' => 'decimal:4', 'sentiment_analyzed_at' => 'datetime'];
   // each review belongs to 1 user (same as getCustomer in Credential)
   public function getUser()
   {
       return $this->belongsTo(User::class, 'user_id', 'id');
   }
   // each review belongs to 1 movie
   public function getMovie()
   {
       return $this->belongsTo(Movie::class, 'movie_id', 'id');
   }
}
