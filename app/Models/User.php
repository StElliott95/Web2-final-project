<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;
    protected $attributes = ['role' => 'user', 'is_active' => true];

    protected $fillable = ['name', 'email', 'password', 'bio', 'avatar_url'];

    protected $hidden = ['password', 'remember_token'];

    protected $casts = ['email_verified_at' => 'datetime', 'password' => 'hashed', 'is_active' => 'boolean'];

    public function socialAccounts(): HasMany
    {
        return $this->hasMany(SocialAccount::class);
    }

    public function calendarConnections(): HasMany
    {
        return $this->hasMany(CalendarConnection::class);
    }

    public function createdDiscussionRooms(): HasMany
    {
        return $this->hasMany(DiscussionRoom::class, 'created_by');
    }

    public function joinedDiscussionRooms(): BelongsToMany
    {
        return $this->belongsToMany(DiscussionRoom::class, 'discussion_room_members', 'user_id', 'room_id')
            ->withPivot(['joined_at', 'left_at'])
            ->withTimestamps();
    }

    public function discussionMessages(): HasMany
    {
        return $this->hasMany(DiscussionMessage::class);
    }
    // Task 4: reviews (one to many)

    public function getReviews()

    {

        return $this->hasMany(Review::class);

    }
 // Task 5: watchlist + watch history (one to many)
   public function getWatchlist()
   {
       return $this->hasMany(WatchlistItem::class);
   }
   public function getWatchHistory()
   {
       return $this->hasMany(WatchHistory::class);
   }
   // Task 9: follow users (many to many on the same table)

    // the users that I follow

    public function getFollowing()

    {

        return $this->belongsToMany(User::class, 'follows', 'follower_id', 'following_id')->withTimestamps();

    }

    // the users that follow me

    public function getFollowers()

    {

        return $this->belongsToMany(User::class, 'follows', 'following_id', 'follower_id')->withTimestamps();

    }

    // do I follow this user?

    public function isFollowing($userId)

    {

        return $this->getFollowing()->where('following_id', $userId)->exists();

    }
 
 
}


