<?php

namespace App\Http\Controllers;

use App\Models\User;

use App\Models\MovieList;

use App\Models\Review;

use App\Models\WatchHistory;
 

use Illuminate\Http\Request;

// Task 9: Follow Users

class FollowController extends Controller

{

    // browse / search users to follow

    function listUsers(Request $request)

    {

        // select * from users where id != me

        $query = User::where('id', '!=', auth()->id());

        // search: where name like '%x%' or email like '%x%' (same as getUsingLike of the doctor)

        if ($request->filled('search')) {

            $query->where(function ($q) use ($request) {

                $q->where('name', 'like', '%'.$request->search.'%')

                    ->orWhere('email', 'like', '%'.$request->search.'%');

            });

        }

        $data = $query->withCount('getFollowers')->orderBy('name')->get();

        // ids of the users that I already follow

        $myFollowing = auth()->user()->getFollowing()->pluck('users.id')->toArray();

        return view('Follows.users', compact('data', 'myFollowing'));

    }

    // follow a user

    function followUser($id)

    {

        $user = User::findOrFail($id);

        if ($user->id == auth()->id()) {

            return redirect()->back()->with('error', 'You cannot follow yourself');

        }

        // add the row only if it does not exist

        auth()->user()->getFollowing()->syncWithoutDetaching([$user->id]);

        return redirect()->back()->with('success', 'You are now following '.$user->name);

    }

    // unfollow a user

    function unfollowUser($id)

    {

        $user = User::findOrFail($id);

        auth()->user()->getFollowing()->detach($user->id);

        return redirect()->back()->with('success', 'You unfollowed '.$user->name);

    }
    // the users that follow this user

    function followers($id)

    {

        $user = User::findOrFail($id);

        $data = $user->getFollowers()->orderBy('name')->get();

        $title = 'Followers of '.$user->name;

        $myFollowing = auth()->user()->getFollowing()->pluck('users.id')->toArray();

        return view('Follows.list', compact('user', 'data', 'title', 'myFollowing'));

    }

    // the users that this user follows

    function following($id)

    {

        $user = User::findOrFail($id);

        $data = $user->getFollowing()->orderBy('name')->get();

        $title = $user->name.' is following';

        $myFollowing = auth()->user()->getFollowing()->pluck('users.id')->toArray();

        return view('Follows.list', compact('user', 'data', 'title', 'myFollowing'));

    }
    // public profile of a user

    function profile($id)

    {

        // withCount = number of followers / following

        $user = User::withCount(['getFollowers', 'getFollowing'])->findOrFail($id);

        $isMe = $user->id == auth()->id();

        $isFollowing = auth()->user()->isFollowing($user->id);

        // the owner sees everything, the others only what he allows

        $reviews = collect();

        if ($isMe || $user->show_reviews) {

            $reviews = Review::with('getMovie')

                ->where('user_id', $user->id)

                ->where('is_hidden', false)

                ->latest()

                ->take(10)

                ->get();

        }

        // only PUBLIC lists (Task 8)

        $lists = collect();

        if ($isMe || $user->show_lists) {

            $lists = MovieList::withCount('movies')

                ->where('user_id', $user->id)

                ->where('is_public', true)

                ->latest()

                ->get();

        }

        // recently watched (Task 5)

        $watched = collect();

        if ($isMe || $user->show_watched) {

            $watched = WatchHistory::with('getMovie')

                ->where('user_id', $user->id)

                ->orderBy('watched_at', 'desc')

                ->take(10)

                ->get();

        }

        return view('Follows.profile', compact('user', 'isMe', 'isFollowing', 'reviews', 'lists', 'watched'));

    }
    // Task 9: recent activity of the users I follow
   function feed()
   {
       // ids of the users that I follow
       $ids = auth()->user()->getFollowing()->pluck('users.id');
       // their reviews (only if they allow reviews on their profile)
       $reviews = Review::with(['getUser', 'getMovie'])
           ->whereIn('user_id', $ids)
           ->where('is_hidden', false)
           ->whereHas('getUser', function ($q) {
               $q->where('show_reviews', true);
           })
           ->latest()->take(20)->get();
       // their PUBLIC lists (Task 8)
       $lists = MovieList::with('user')
           ->whereIn('user_id', $ids)
           ->where('is_public', true)
           ->whereHas('user', function ($q) {
               $q->where('show_lists', true);
           })
           ->latest()->take(20)->get();
       // the movies they watched (Task 5)
       $watched = WatchHistory::with(['getUser', 'getMovie'])
           ->whereIn('user_id', $ids)
           ->whereHas('getUser', function ($q) {
               $q->where('show_watched', true);
           })
           ->orderBy('watched_at', 'desc')->take(20)->get();
       // put everything in 1 list
       $activities = collect();
       foreach ($reviews as $row) {
           $activities->push([
               'user' => $row->getUser,
               'text' => 'rated "'.$row->getMovie->title.'" '.$row->rating.'/10',
               'link' => route('list-reviews', $row->movie_id),
               'date' => $row->created_at,
           ]);
       }
       foreach ($lists as $row) {
           $activities->push([
               'user' => $row->user,
               'text' => 'created the public list "'.$row->title.'"',
               'link' => route('lists.show', $row->id),
               'date' => $row->created_at,
           ]);
       }
       foreach ($watched as $row) {
           $activities->push([
               'user' => $row->getUser,
               'text' => 'watched "'.$row->getMovie->title.'"',
               'link' => null,
               'date' => $row->watched_at,
           ]);
       }
       // newest first
       $activities = $activities->sortByDesc('date')->take(30);
       return view('Follows.feed', compact('activities'));
   }
// Task 9: privacy settings page

    function privacySettings()

    {

        $user = auth()->user();

        return view('Follows.privacy', compact('user'));

    }

    // save the privacy settings

    function updatePrivacy(Request $request)

    {

        $user = auth()->user();

        // an unchecked checkbox is not sent by the browser, so boolean() gives false

        $user->show_reviews = $request->boolean('show_reviews');

        $user->show_lists = $request->boolean('show_lists');

        $user->show_watched = $request->boolean('show_watched');

        $user->save();

        return redirect()->route('privacy-settings')->with('success', 'Privacy settings saved');

    }
 
 
 

}
 