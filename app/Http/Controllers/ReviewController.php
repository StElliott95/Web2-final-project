<?php
namespace App\Http\Controllers;
use App\Models\Movie;
use App\Models\Review;
use Illuminate\Http\Request;
class ReviewController extends Controller
{
   // movie page: all reviews + average rating + my review
   function listReviews($movieId)
   {
       $movie = Movie::findOrFail($movieId);
       // select * from reviews where movie_id = ? and is_hidden = 0 order by created_at desc
       $data = Review::with('getUser')
           ->where('movie_id', $movieId)
           ->where('is_hidden', false)
           ->latest()
           ->get();
       // select avg(rating) from reviews where movie_id = ? and is_hidden = 0
       $average = Review::where('movie_id', $movieId)
           ->where('is_hidden', false)
           ->avg('rating');
       // my review on this movie (null if I did not review it yet)
       $myReview = Review::where('movie_id', $movieId)
           ->where('user_id', auth()->id())
           ->first();
       return view('Reviews.list', compact('movie', 'data', 'average', 'myReview'));
   }
   // add a review
   function saveReview(Request $request, $movieId)
   {
       Movie::findOrFail($movieId);
       $request->validate([
           'rating' => 'required|integer|min:1|max:10',
           'body' => 'nullable|string|max:2000',
       ]);
       // each user can review a movie only once
       $exists = Review::where('movie_id', $movieId)
           ->where('user_id', auth()->id())
           ->exists();
       if ($exists) {
           return redirect()->back()->with('error', 'You already reviewed this movie');
       }
       $row = new Review();
       $row->user_id = auth()->id();
       $row->movie_id = $movieId;
       $row->rating = $request->rating;
       $row->body = $request->body;
       $row->save();
       return redirect()->route('list-reviews', $movieId)->with('success', 'Review added');
   }
   // show the edit form
   function editReview($id)
   {
       $row = $this->findMyReview($id);
       return view('Reviews.edit', compact('row'));
   }
   // save the edit (same idea as update2 of the doctor)
   function updateReview($id, Request $request)
   {
       $row = $this->findMyReview($id);
       $request->validate([
           'rating' => 'required|integer|min:1|max:10',
           'body' => 'nullable|string|max:2000',
       ]);
       $row->fill($request->only(['rating', 'body']));
       if ($row->isClean()) {
           return redirect()->route('list-reviews', $row->movie_id)->with('success', 'Nothing changed');
       }
       $row->save();
       return redirect()->route('list-reviews', $row->movie_id)->with('success', 'Review updated');
   }
   // delete my review
   function deleteReview($id)
   {
       $row = $this->findMyReview($id);
       $movieId = $row->movie_id;
       $row->delete();
       return redirect()->route('list-reviews', $movieId)->with('success', 'Review deleted');
   }
   // a user can edit / delete only HIS review
   private function findMyReview($id)
   {
       $row = Review::findOrFail($id);
       if ($row->user_id != auth()->id()) {
           abort(403, 'This is not your review');
       }
       return $row;
   }
   // ================= ADMIN =================
   // list all reviews (visible + hidden)
   function adminReviews()
   {
       $this->checkAdmin();
       $data = Review::with(['getUser', 'getMovie'])->latest()->get();
       return view('Reviews.admin', compact('data'));
   }
   // hide / show a review
   function toggleReview($id)
   {
       $this->checkAdmin();
       $row = Review::findOrFail($id);
       $row->is_hidden = ! $row->is_hidden;
       $row->save();
       return redirect()->route('admin-reviews')->with('success', 'Review updated');
   }
   // admin deletes any review
   function adminDeleteReview($id)
   {
       $this->checkAdmin();
       $row = Review::findOrFail($id);
       $row->delete();
       return redirect()->route('admin-reviews')->with('success', 'Review deleted');
   }
   // only admins can continue
  private function checkAdmin()
   {
       if (auth()->user()->role != 'admin') {
           abort(403, 'Admins only');
       }
   }
}