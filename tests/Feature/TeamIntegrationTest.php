<?php
namespace Tests\Feature;
use App\Models\{User,Movie,Genre,Person,Review,WatchHistory,MovieList,MoviePreference};
use App\Services\RecommendationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
class TeamIntegrationTest extends TestCase {
    use RefreshDatabase;
    protected function setUp(): void {
        parent::setUp();
        $this->seed(\Database\Seeders\TeamDemoSeeder::class);
    }
    private function member(): User { return User::where('email','maya@frame.test')->first(); }
    private function admin(): User { return User::where('role','admin')->first(); }
    public function test_discovery_and_tmdb_detail_fallback_render(): void {
        config(['tmdb.read_access_token'=>'']);
        $this->get('/')->assertOk()->assertSee('Interstellar');
        $this->get('/?q=Arrival')->assertOk()->assertSee('Arrival');
        $this->get('/movies/157336')->assertOk()->assertSee('Interstellar');
    }
    public function test_login_uses_hashed_team_password_and_role(): void {
        $this->post('/login',['email'=>'maya@frame.test','password'=>'FrameDemo!2026'])->assertRedirect('/');
        $this->assertAuthenticatedAs($this->member());
        $this->get('/admin')->assertForbidden();
        $this->post('/logout')->assertRedirect('/');$this->assertGuest();
    }
    public function test_all_member_and_admin_views_render(): void {
        $this->actingAs($this->member());
        foreach(['/recommendations','/preferences','/lists','/lists/create','/community/lists','/watchlist','/watch-history'] as $url) $this->get($url)->assertOk();
        $this->actingAs($this->admin());
        foreach(['/admin','/admin/users','/admin/reviews','/admin/movies','/admin/movies/create','/admin/movies/'.Movie::first()->id.'/edit','/admin/activity'] as $url) $this->get($url)->assertOk();
    }
    public function test_recommendations_use_normalized_preferences_and_exclude_history(): void {
        $user=$this->member();$result=app(RecommendationService::class)->forUser($user);
        $this->assertCount(4,$result);
        $this->assertNotContains(157336,$result->pluck('movie.tmdb_id')->all());
        $this->assertNotContains(27205,$result->pluck('movie.tmdb_id')->all());
        $blade=$result->first(fn($r)=>$r['movie']->tmdb_id===335984);
        $this->assertContains('Matches your favorite actors',$blade['reasons']);
        $this->actingAs($user)->get('/recommendations')->assertSee('/movies/335984',false);
    }
    public function test_low_rating_reduces_similarity_score(): void {
        $user=$this->member();$service=app(RecommendationService::class);
        $before=$service->forUser($user)->first(fn($r)=>$r['movie']->tmdb_id===155)['score'];
        Review::where('user_id',$user->id)->update(['rating'=>1]);
        $after=$service->forUser($user)->first(fn($r)=>$r['movie']->tmdb_id===155)['score'];
        $this->assertLessThan($before,$after);
    }
    public function test_preferences_validate_ids_and_can_be_cleared(): void {
        $this->actingAs($this->member())->put('/preferences',['genres'=>[999999]])->assertSessionHasErrors('genres.0');
        $this->put('/preferences',[])->assertRedirect('/recommendations');
        $this->assertSame([],MoviePreference::where('user_id',$this->member()->id)->first()->genres);
    }
    public function test_private_list_is_not_exposed_to_other_users_or_guests(): void {
        $list=MovieList::where('is_public',false)->first();
        $this->get('/lists/'.$list->id)->assertForbidden();
        $this->get('/community/lists')->assertDontSee($list->title);
        $this->actingAs($this->admin())->get('/lists/'.$list->id)->assertForbidden();
        $this->actingAs($this->member())->get('/lists/'.$list->id)->assertOk();
    }
    public function test_only_owner_can_mutate_even_a_public_list(): void {
        $list=MovieList::where('is_public',true)->first();$movie=Movie::first();
        $this->actingAs($this->admin());
        $this->put('/lists/'.$list->id,['title'=>'Wrong owner','is_public'=>1])->assertForbidden();
        $this->post('/lists/'.$list->id.'/movies',['movie_id'=>$movie->id])->assertForbidden();
        $this->delete('/lists/'.$list->id.'/movies/'.$movie->id)->assertForbidden();
        $this->delete('/lists/'.$list->id)->assertForbidden();
    }
    public function test_list_crud_and_unique_memberships(): void {
        $this->actingAs($this->member());
        $this->post('/lists',['title'=>'New list','is_public'=>0])->assertRedirect();
        $list=MovieList::where('title','New list')->first();$movie=Movie::first();
        $this->assertEquals($this->member()->id,$list->user_id);
        $this->put('/lists/'.$list->id,['title'=>'Renamed','description'=>'Updated','is_public'=>1])->assertRedirect();
        $this->get('/lists/'.$list->id.'/edit')->assertOk();
        for($i=0;$i<2;$i++) $this->post('/lists/'.$list->id.'/movies',['movie_id'=>$movie->id])->assertRedirect();
        $this->assertEquals(1,$list->movies()->count());
        $this->get('/lists/'.$list->id)->assertOk()->assertSee('/movies/'.$movie->tmdb_id,false);
        $this->delete('/lists/'.$list->id.'/movies/'.$movie->id)->assertRedirect();$this->assertEquals(0,$list->movies()->count());
        $this->delete('/lists/'.$list->id)->assertRedirect('/lists');
    }
    public function test_non_admin_is_rejected_by_every_admin_write(): void {
        $this->actingAs($this->member());$movie=Movie::first();$review=Review::first();$admin=$this->admin();
        $this->post('/admin/movies',[])->assertForbidden();
        $this->put('/admin/movies/'.$movie->id,[])->assertForbidden();
        $this->delete('/admin/movies/'.$movie->id)->assertForbidden();
        $this->put('/admin/users/'.$admin->id,[])->assertForbidden();
        $this->delete('/admin/users/'.$admin->id)->assertForbidden();
        $this->put('/admin/reviews/'.$review->id.'/toggle')->assertForbidden();
        $this->delete('/admin/reviews/'.$review->id)->assertForbidden();
    }
    public function test_existing_moderation_uses_hidden_flag_and_ten_point_ratings(): void {
        $review=Review::where('rating',8)->first();$this->actingAs($this->admin());
        $this->put('/admin/reviews/'.$review->id.'/toggle')->assertRedirect('/admin/reviews');
        $this->assertEquals(1,$review->fresh()->is_hidden);
        $this->get('/movies/'.$review->movie_id.'/reviews')->assertDontSee($review->body);
        $this->get('/admin')->assertViewHas('rated',fn($movies)=>!$movies->contains('id',$review->movie_id));
        $this->put('/admin/reviews/'.$review->id.'/toggle')->assertRedirect();
        $this->get('/movies/'.$review->movie_id.'/reviews')->assertSee($review->body);
    }
    public function test_suspended_users_cannot_login_or_keep_session(): void {
        $member=$this->member();$this->actingAs($this->admin())->put('/admin/users/'.$member->id,['role'=>'user','is_active'=>0])->assertRedirect();
        $this->actingAs($member->fresh())->get('/recommendations')->assertRedirect('/login');$this->assertGuest();
        $this->post('/login',['email'=>$member->email,'password'=>'FrameDemo!2026'])->assertSessionHasErrors('email');
    }
    public function test_admin_cannot_demote_suspend_or_delete_self(): void {
        $admin=$this->admin();$this->actingAs($admin);
        $this->put('/admin/users/'.$admin->id,['role'=>'user','is_active'=>0])->assertStatus(422);
        $this->delete('/admin/users/'.$admin->id)->assertStatus(422);
    }
    public function test_movie_crud_updates_normalized_relations(): void {
        $this->actingAs($this->admin());
        $data=['tmdb_id'=>99999999,'title'=>'Integration test film','overview'=>'Example','runtime'=>100,'vote_average'=>8,'genres'=>[Genre::first()->id],'actors'=>[Person::where('name','Ryan Gosling')->first()->id],'directors'=>[Person::where('name','Christopher Nolan')->first()->id]];
        $this->post('/admin/movies',$data)->assertRedirect('/admin/movies');
        $movie=Movie::where('tmdb_id',99999999)->first();
        $this->assertEquals(1,$movie->genres()->count());$this->assertEquals(2,$movie->credits()->count());
        $this->put('/admin/movies/'.$movie->id,array_merge($data,['title'=>'Changed','actors'=>[]]))->assertRedirect();
        $this->assertEquals('Changed',$movie->fresh()->title);$this->assertEquals(1,$movie->credits()->count());
        $this->delete('/admin/movies/'.$movie->id)->assertRedirect();$this->assertDatabaseMissing('movies',['id'=>$movie->id]);
    }
    public function test_dashboard_counts_deduplicate_repeat_watches(): void {
        $watch=WatchHistory::first();
        WatchHistory::create(['user_id'=>$watch->user_id,'movie_id'=>$watch->movie_id,'watched_at'=>now()]);
        $this->actingAs($this->admin())->get('/admin')->assertViewHas('counts',fn($c)=>$c['Registered users']===3 && $c['Watch records']===4)
            ->assertViewHas('watched',fn($movies)=>(int)$movies->firstWhere('id',$watch->movie_id)->total===2);
    }
    public function test_activity_records_review_changes_without_review_text(): void {
        $review=Review::first();$this->actingAs($this->admin())->put('/admin/reviews/'.$review->id.'/toggle');
        $this->assertDatabaseHas('activities',['user_id'=>$this->admin()->id,'action'=>'Updated Review','subject'=>'#'.$review->id]);
    }
}
