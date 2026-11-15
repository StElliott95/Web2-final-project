<?php
namespace Tests\Feature;
use App\Models\{Community,DiscussionRoom,Genre,Movie,MoviePreference,Person,Review,User,WatchHistory};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
class CommunityRecommendationTest extends TestCase {
    use RefreshDatabase;
    private function movie(int $id): Movie { return Movie::create(['tmdb_id'=>900000+$id,'title'=>'Test film '.$id,'adult'=>false,'vote_average'=>5]); }
    private function community(User $user,string $name='Test club'): Community {
        $club=Community::create(['created_by'=>$user->id,'name'=>$name]);
        $club->members()->attach($user);
        return $club;
    }
    public function test_recommendations_page_through_all_unseen_movies_without_duplicates(): void {
        $user=User::factory()->create();
        for($i=1;$i<=27;$i++) $this->movie($i);
        $this->actingAs($user);
        $seen=[];
        foreach([1=>12,2=>12,3=>3] as $page=>$count) {
            $response=$this->get('/recommendations?page='.$page)->assertOk();
            $items=$response->viewData('recommendations');
            $this->assertSame(27,$items->total());$this->assertCount($count,$items->items());
            foreach($items as $item) $seen[]=$item['movie']->id;
        }
        $this->assertCount(27,array_unique($seen));
        $this->get('/recommendations?page=0')->assertSessionHasErrors('page');
    }
    public function test_saving_preferences_changes_first_result_even_with_strong_history(): void {
        $user=User::factory()->create();
        $action=Genre::create(['tmdb_id'=>901,'name'=>'Action']);$drama=Genre::create(['tmdb_id'=>902,'name'=>'Drama']);
        $first=$this->movie(1);$first->genres()->attach($action);
        $second=$this->movie(2);$second->genres()->attach($drama);
        for($i=3;$i<=12;$i++) { $seed=$this->movie($i);$seed->genres()->attach($action);Review::create(['user_id'=>$user->id,'movie_id'=>$seed->id,'rating'=>10,'body'=>'Loved it','is_hidden'=>false]); }
        $this->actingAs($user)->put('/preferences',['genres'=>[$action->id]])->assertRedirect('/recommendations');
        $this->assertSame($first->id,$this->get('/recommendations')->viewData('recommendations')->items()[0]['movie']->id);
        $this->put('/preferences',['genres'=>[$drama->id]])->assertRedirect('/recommendations');
        $response=$this->get('/recommendations')->assertSee('Drama');
        $this->assertSame($second->id,$response->viewData('recommendations')->items()[0]['movie']->id);
        $this->assertSame([$drama->id],MoviePreference::where('user_id',$user->id)->first()->genres);
        $this->put('/preferences',[])->assertRedirect('/recommendations');
        $this->get('/recommendations')->assertSee('None selected');
    }
    public function test_no_matching_favorites_explains_catalogue_limit_and_error_form_keeps_unchecked_values(): void {
        $user=User::factory()->create();$this->movie(1);
        $genre=Genre::create(['tmdb_id'=>999,'name'=>'Rare genre']);
        $this->actingAs($user)->put('/preferences',['genres'=>[$genre->id]]);
        $this->get('/recommendations')->assertSee('No unseen saved movies match');
        $this->from('/preferences')->put('/preferences',['actors'=>[99999]])->assertSessionHasErrors();
        $this->get('/preferences')->assertDontSee('checked',false);
    }
    public function test_community_directory_pagination_and_search_are_separate_from_lists(): void {
        $user=User::factory()->create();
        for($i=1;$i<=14;$i++) $this->community($user,sprintf('Club %02d',$i));
        $this->get('/communities?q=Club')->assertOk()->assertSee('Club 01')->assertDontSee('Club 14')->assertSee('page=2');
        $this->get('/communities?q=Club&page=2')->assertOk()->assertSee('Club 14')->assertDontSee('Club 01');
        $this->get('/community/lists')->assertRedirect('/lists/public');
        $this->get('/lists/public')->assertOk()->assertSee('Public movie lists')->assertDontSee('Club 01');
    }
    public function test_create_join_chat_thread_and_message_flow(): void {
        $owner=User::factory()->create();$member=User::factory()->create();$movie=$this->movie(1);
        $this->actingAs($owner)->post('/communities',['name'=>'Cinema Club','description'=>'Talk movies'])->assertRedirect();
        $club=Community::firstOrFail();$chat=$club->rooms()->whereNull('movie_id')->firstOrFail();
        $this->assertDatabaseHas('community_user',['community_id'=>$club->id,'user_id'=>$owner->id]);
        $this->actingAs($member)->post('/communities/'.$club->id.'/join')->assertRedirect();
        $this->get(route('communities.room',[$club,$chat]))->assertOk()->assertSee('General chat');
        $this->postJson(route('integrations.discussion-messages',$chat),['message'=>'Hello club'])->assertCreated()->assertJsonPath('message','Hello club');
        $this->post('/communities/'.$club->id.'/threads',['movie_id'=>$movie->id,'title'=>'Discuss the ending'])->assertRedirect();
        $thread=$club->rooms()->whereNotNull('movie_id')->firstOrFail();
        $this->get(route('communities.room',[$club,$thread]))->assertOk()->assertSee('Discuss the ending');
        $this->postJson(route('integrations.discussion-messages',$thread),['message'=>'My theory'])->assertCreated();
        $this->assertSame(1,$chat->messages()->count());$this->assertSame(1,$thread->messages()->count());
    }
    public function test_nonmembers_cannot_read_post_stream_or_create_threads_and_scope_is_enforced(): void {
        $owner=User::factory()->create();$outsider=User::factory()->create();$club=$this->community($owner);
        $other=$this->community($owner,'Another');$movie=$this->movie(1);
        $room=$club->rooms()->create(['created_by'=>$owner->id,'title'=>'General chat','is_active'=>true]);
        $this->actingAs($outsider);
        $this->get(route('communities.room',[$club,$room]))->assertForbidden();
        $this->postJson(route('integrations.discussion-messages',$room),['message'=>'No access'])->assertForbidden();
        $this->get(route('integrations.discussion-stream',$room))->assertForbidden();
        $this->post(route('communities.threads.store',$club),['movie_id'=>$movie->id,'title'=>'No access'])->assertForbidden();
        $this->actingAs($owner)->get(route('communities.room',[$other,$room]))->assertNotFound();
    }
    public function test_chat_history_and_movie_threads_are_paginated(): void {
        $user=User::factory()->create();$club=$this->community($user);$movie=$this->movie(1);
        $chat=$club->rooms()->create(['created_by'=>$user->id,'title'=>'General chat','is_active'=>true]);
        for($i=1;$i<=31;$i++) $chat->messages()->create(['user_id'=>$user->id,'message'=>sprintf('Message %03d',$i)]);
        for($i=1;$i<=13;$i++) $club->rooms()->create(['created_by'=>$user->id,'movie_id'=>$movie->id,'title'=>sprintf('Thread %03d',$i),'is_active'=>true]);
        $this->actingAs($user)->get(route('communities.room',[$club,$chat]))->assertSee('Message 031')->assertDontSee('Message 001');
        $this->get(route('communities.room',[$club,$chat]).'?page=2')->assertSee('Message 001')->assertDontSee('Message 031')->assertSee('Back to live chat');
        $this->get(route('communities.show',$club))->assertSee('Thread 013')->assertDontSee('Thread 001');
        $this->get(route('communities.show',$club).'?page=2')->assertSee('Thread 001')->assertDontSee('Thread 013');
    }
    public function test_global_movie_discussion_does_not_join_a_community_room(): void {
        $user=User::factory()->create();$club=$this->community($user);$movie=$this->movie(1);
        $club->rooms()->create(['created_by'=>$user->id,'movie_id'=>$movie->id,'title'=>'Community thread','is_active'=>true]);
        $this->actingAs($user)->get(route('integrations.discussion-room',$movie))->assertOk()->assertDontSee('Community thread');
        $this->assertSame(1,DiscussionRoom::whereNull('community_id')->where('movie_id',$movie->id)->count());
    }
}
