<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
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
  // Task 4/5: positive / neutral / negative counts for each movie
  // select movie_id, sentiment_label, count(*) as total from reviews
  // where movie_id in (...) and is_hidden = 0 and sentiment_label is not null
  // group by movie_id, sentiment_label
  // returns [movie_id => ['positive' => 3, 'neutral' => 0, 'negative' => 1, 'total' => 4]]
  public static function sentimentSummaries($movieIds)
  {
      $rows = self::select('movie_id', 'sentiment_label', DB::raw('count(*) as total'))
          ->whereIn('movie_id', $movieIds)
          ->where('is_hidden', false)
          ->whereIn('sentiment_label', ['positive', 'neutral', 'negative'])
          ->groupBy('movie_id', 'sentiment_label')
          ->get();
      $summaries = [];
      foreach ($rows as $row) {
          if (! isset($summaries[$row->movie_id])) {
              $summaries[$row->movie_id] = ['positive' => 0, 'neutral' => 0, 'negative' => 0, 'total' => 0];
          }
          $summaries[$row->movie_id][$row->sentiment_label] += (int) $row->total;
          $summaries[$row->movie_id]['total'] += (int) $row->total;
      }
      return $summaries;
  }
}