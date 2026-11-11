<?php
namespace App\Http\Controllers;
use App\Models\Movie;
use App\Models\WatchHistory;
use App\Models\WatchlistItem;
use Illuminate\Http\Request;
class WatchlistController extends Controller
{
   // my watchlist page
   function listWatchlist()
   {
       // select * from watchlist_items where user_id = ? and is_watched = 0
       $toWatch = WatchlistItem::with('getMovie')
           ->where('user_id', auth()->id())
           ->where('is_watched', false)
           ->latest()
           ->get();
       $watched = WatchlistItem::with('getMovie')
           ->where('user_id', auth()->id())
           ->where('is_watched', true)
           ->orderBy('watched_at', 'desc')
           ->get();
       // movies that are NOT in my watchlist yet (for the dropdown)
       $inList = WatchlistItem::where('user_id', auth()->id())->pluck('movie_id');
       $movies = Movie::whereNotIn('id', $inList)->orderBy('title')->get();
       return view('Watchlist.list', compact('toWatch', 'watched', 'movies'));
   }
   // add a movie
   function addMovie(Request $request)
   {
       $request->validate([
           'movie_id' => 'required|integer|exists:movies,id',
       ]);
       // if it exists do nothing, else create it
       WatchlistItem::firstOrCreate([
           'user_id' => auth()->id(),
           'movie_id' => $request->movie_id,
       ]);
       return redirect()->route('list-watchlist')->with('success', 'Movie added to your watchlist');
   }
   // remove a movie
   function removeMovie($id)
   {
       $row = $this->findMyItem($id);
       $row->delete();
       return redirect()->route('list-watchlist')->with('success', 'Movie removed');
   }
   // mark as watched + save in history
   function markWatched($id)
   {
       $row = $this->findMyItem($id);
       $row->is_watched = true;
       $row->watched_at = now();
       $row->save();
       $history = new WatchHistory();
       $history->user_id = auth()->id();
       $history->movie_id = $row->movie_id;
       $history->watched_at = now();
       $history->save();
       return redirect()->route('list-watchlist')->with('success', 'Marked as watched');
   }
   // back to "to watch"
   function markUnwatched($id)
   {
       $row = $this->findMyItem($id);
       $row->is_watched = false;
       $row->watched_at = null;
       $row->save();
       return redirect()->route('list-watchlist')->with('success', 'Moved back to "to watch"');
   }
   // watch history page
   function history()
   {
       $data = WatchHistory::with('getMovie')
           ->where('user_id', auth()->id())
           ->orderBy('watched_at', 'desc')
           ->get();
       return view('Watchlist.history', compact('data'));
   }
   // a user can only change HIS watchlist
   private function findMyItem($id)
   {
       $row = WatchlistItem::findOrFail($id);
       if ($row->user_id != auth()->id()) {
           abort(403, 'This is not your watchlist');
       }
       return $row;
   }
}