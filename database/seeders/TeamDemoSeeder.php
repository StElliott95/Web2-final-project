<?php
namespace Database\Seeders;
use App\Models\{User,Movie,Genre,Person,Review,WatchHistory,MoviePreference,MovieList};
use Illuminate\Database\Seeder;
class TeamDemoSeeder extends Seeder {
    public function run(): void {
        if (!app()->environment(['local','testing'])) throw new \RuntimeException('Demo data is local/testing only.');
        $admin=User::firstOrCreate(['email'=>'admin@frame.test'],['name'=>'Team Admin','password'=>'FrameAdmin!2026']);
        if ($admin->wasRecentlyCreated) $admin->forceFill(['role'=>'admin'])->save();
        $member=User::firstOrCreate(['email'=>'maya@frame.test'],['name'=>'Maya Haddad','password'=>'FrameDemo!2026']);
        $other=User::firstOrCreate(['email'=>'adam@frame.test'],['name'=>'Adam Khoury','password'=>'FrameDemo!2026']);
        $science=Genre::firstOrCreate(['tmdb_id'=>878],['name'=>'Science Fiction']);
        $drama=Genre::firstOrCreate(['tmdb_id'=>18],['name'=>'Drama']);
        $nolan=Person::firstOrCreate(['tmdb_person_id'=>525],['name'=>'Christopher Nolan','known_for_department'=>'Directing']);
        $denis=Person::firstOrCreate(['tmdb_person_id'=>137427],['name'=>'Denis Villeneuve','known_for_department'=>'Directing']);
        $ryan=Person::firstOrCreate(['tmdb_person_id'=>30614],['name'=>'Ryan Gosling','known_for_department'=>'Acting']);
        $data=[
            [157336,'Interstellar','2014-11-07',169,8.7,$nolan,'An astronaut travels beyond our solar system in search of a future for humanity.'],
            [27205,'Inception','2010-07-16',148,8.8,$nolan,'A team enters dreams on an assignment to plant an idea.'],
            [329865,'Arrival','2016-11-11',116,7.9,$denis,'A linguist works to understand visitors from another world.'],
            [335984,'Blade Runner 2049','2017-10-06',164,8.0,$denis,'An investigator searches for a missing blade runner in a future Los Angeles.'],
            [438631,'Dune','2021-10-22',155,8.0,$denis,'A young heir confronts the politics and dangers of a desert planet.'],
            [155,'The Dark Knight','2008-07-18',152,9.0,$nolan,'Gotham faces a criminal who challenges its protector and its institutions.']
        ];
        $movies=[];
        foreach($data as [$id,$title,$date,$runtime,$rating,$director,$overview]) {
            $movie=Movie::firstOrCreate(['tmdb_id'=>$id],['title'=>$title,'release_date'=>$date,'runtime'=>$runtime,'vote_average'=>$rating,'overview'=>$overview,'popularity'=>$rating*10,'adult'=>false]);
            $movie->genres()->syncWithoutDetaching([$drama->id,...($id===155?[]:[$science->id])]);
            $movie->credits()->firstOrCreate(['person_id'=>$director->id,'credit_type'=>'crew','job'=>'Director'],['department'=>'Directing']);
            if($id===335984) $movie->credits()->firstOrCreate(['person_id'=>$ryan->id,'credit_type'=>'cast'],['cast_order'=>0]);
            $movies[$id]=$movie;
        }
        MoviePreference::firstOrCreate(['user_id'=>$member->id],['genres'=>[$science->id,$drama->id],'actors'=>[$ryan->id],'directors'=>[$nolan->id]]);
        foreach([157336,27205] as $id) {
            WatchHistory::firstOrCreate(['user_id'=>$member->id,'movie_id'=>$movies[$id]->id],['watched_at'=>now()->subDays(2)]);
            Review::firstOrCreate(['user_id'=>$member->id,'movie_id'=>$movies[$id]->id],['rating'=>9,'body'=>'A memorable film with ideas worth discussing.','is_hidden'=>false]);
        }
        WatchHistory::firstOrCreate(['user_id'=>$other->id,'movie_id'=>$movies[157336]->id],['watched_at'=>now()->subDay()]);
        Review::firstOrCreate(['user_id'=>$other->id,'movie_id'=>$movies[329865]->id],['rating'=>8,'body'=>'Thoughtful storytelling and an engaging central mystery.','is_hidden'=>false]);
        $list=MovieList::firstOrCreate(['user_id'=>$member->id,'title'=>'Science fiction favorites'],['description'=>'Stories that explore new worlds and big ideas.','is_public'=>true]);
        $list->movies()->syncWithoutDetaching([$movies[157336]->id,$movies[329865]->id]);
        MovieList::firstOrCreate(['user_id'=>$member->id,'title'=>'Weekend shortlist'],['description'=>'My private collection.','is_public'=>false]);
    }
}
