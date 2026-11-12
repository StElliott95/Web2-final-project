<?php
namespace App\Services;
use App\Models\{Movie,MoviePreference,Review,User,WatchHistory};
use Illuminate\Support\Collection;
class RecommendationService
{
    public function forUser(User $user, int $limit = 12): Collection {
        $preferences = MoviePreference::where('user_id',$user->id)->first();
        $watched = WatchHistory::where('user_id',$user->id)->distinct()->pluck('movie_id');
        $ratings = Review::where('user_id',$user->id)->pluck('rating','movie_id');
        $excluded = $watched->merge($ratings->keys())->unique();
        $movies = Movie::with(['genres','credits.person'])->where('adult',false)->get();
        $seeds = $movies->whereIn('id',$excluded);
        return $movies->whereNotIn('id',$excluded)->map(function($movie) use ($preferences,$seeds,$ratings) {
            $features = $this->features($movie);
            $score = (float)$movie->vote_average / 10;
            $reasons = [];
            foreach (['genres'=>3,'actors'=>4,'directors'=>4] as $key=>$weight) {
                $matches = array_intersect($features[$key],$preferences?->$key ?? []);
                $score += count($matches)*$weight;
                if ($matches) $reasons[] = 'Matches your favorite '.$key;
            }
            $best = 0; $similarTitle = null;
            foreach ($seeds as $seed) {
                $other = $this->features($seed);
                $union = count(array_unique(array_merge($features['genres'],$other['genres'])));
                $similarity = ($union ? 2*count(array_intersect($features['genres'],$other['genres']))/$union : 0)
                    + .75*count(array_intersect($features['actors'],$other['actors']))
                    + 1.5*count(array_intersect($features['directors'],$other['directors']));
                // Team reviews use 1–10. Ratings below 5.5 are negative feedback.
                $weight = $ratings->has($seed->id) ? ($ratings[$seed->id]-5.5)/1.5 : 1;
                $contribution = $weight*$similarity; $score += $contribution;
                if ($contribution > $best) { $best=$contribution; $similarTitle=$seed->title; }
            }
            if ($similarTitle) $reasons[]='Similar to '.$similarTitle;
            return ['movie'=>$movie,'score'=>$score,'reasons'=>$reasons ?: ['Highly rated in the saved catalogue']];
        })->sort(fn($a,$b)=>($b['score']<=>$a['score']) ?: ($a['movie']->id<=>$b['movie']->id))->take($limit)->values();
    }
    private function features(Movie $movie): array {
        return [
            'genres'=>$movie->genres->pluck('id')->all(),
            'actors'=>$movie->credits->where('credit_type','cast')->pluck('person_id')->unique()->values()->all(),
            'directors'=>$movie->credits->where('credit_type','crew')->where('job','Director')->pluck('person_id')->unique()->values()->all(),
        ];
    }
}
