<?php
namespace App\Providers;
use App\Models\{Activity,User,Movie,Review,WatchHistory,WatchlistItem,MovieList,MoviePreference};
use App\Observers\ActivityObserver;
use Illuminate\Auth\Events\Login;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
class AdminActivityServiceProvider extends ServiceProvider {
    public function boot(): void {
        foreach ([User::class,Movie::class,Review::class,WatchHistory::class,WatchlistItem::class,MovieList::class,MoviePreference::class] as $model) $model::observe(ActivityObserver::class);
        Event::listen(Login::class,function(Login $event) {
            if ($event->user->is_active) Activity::create(['user_id'=>$event->user->id,'action'=>'Signed in']);
        });
    }
}
