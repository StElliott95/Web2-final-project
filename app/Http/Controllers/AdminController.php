<?php
namespace App\Http\Controllers;
use App\Models\{User,Movie,Genre,Person,Review,WatchHistory,Activity};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
class AdminController extends Controller
{
    public function index() {
        $watchCounts=DB::table('watch_histories')->select('movie_id')->selectRaw('COUNT(DISTINCT user_id) as total')->groupBy('movie_id');
        $watched=Movie::joinSub($watchCounts,'w',fn($join)=>$join->on('movies.id','=','w.movie_id'))->orderByDesc('total')->limit(5)->get(['movies.*','total']);
        $rated=Movie::whereHas('getReviews',fn($q)=>$q->where('is_hidden',false))->withAvg(['getReviews'=>fn($q)=>$q->where('is_hidden',false)],'rating')->orderByDesc('get_reviews_avg_rating')->limit(5)->get();
        $genres=DB::table('genres')->join('movie_genre','genres.id','=','movie_genre.genre_id')->joinSub($watchCounts,'w',fn($join)=>$join->on('movie_genre.movie_id','=','w.movie_id'))->select('genres.name')->selectRaw('SUM(w.total) as total')->groupBy('genres.id','genres.name')->orderByDesc('total')->limit(8)->get();
        return view('admin.dashboard',['counts'=>['Registered users'=>User::count(),'Reviews'=>Review::count(),'Movies'=>Movie::count(),'Watch records'=>WatchHistory::count()],'watched'=>$watched,'rated'=>$rated,'genres'=>$genres,'activities'=>Activity::with('user')->latest()->limit(12)->get()]);
    }
    public function users(Request $request) {
        $request->validate(['q'=>'nullable|string|max:100']);
        return view('admin.users',['users'=>User::when($request->filled('q'),fn($q)=>$q->where(fn($q)=>$q->where('name','like','%'.$request->input('q').'%')->orWhere('email','like','%'.$request->input('q').'%')))->orderBy('name')->paginate(15)->withQueryString()]);
    }
    public function updateUser(Request $request,User $user) {
        $data=$request->validate(['role'=>'required|in:user,admin','is_active'=>'required|boolean']);
        abort_if($user->id===$request->user()->id,422,'You cannot change your own access.');
        DB::transaction(function() use($user,$data) {
            $admins=User::where('role','admin')->where('is_active',true)->lockForUpdate()->get();
            abort_if($user->role==='admin' && $user->is_active && ($data['role']!=='admin'||!$data['is_active']) && $admins->count()<=1,422,'Keep at least one active administrator.');
            $user->forceFill($data)->save();
        });
        return back()->with('success','User access updated.');
    }
    public function deleteUser(Request $request,User $user) {
        abort_if($user->id===$request->user()->id,422,'You cannot delete your own account.');
        DB::transaction(function() use($user) {
            $admins=User::where('role','admin')->where('is_active',true)->lockForUpdate()->get();
            abort_if($user->role==='admin' && $user->is_active && $admins->count()<=1,422,'Keep at least one active administrator.');
            $user->delete();
        });
        return back()->with('success','User deleted.');
    }
    public function movies(Request $request) {
        $request->validate(['q'=>'nullable|string|max:100']);
        return view('admin.movies',['movies'=>Movie::when($request->filled('q'),fn($q)=>$q->where('title','like','%'.$request->input('q').'%'))->orderBy('title')->paginate(15)->withQueryString()]);
    }
    public function createMovie() { return $this->form(new Movie); }
    public function editMovie(Movie $movie) { return $this->form($movie); }
    private function form(Movie $movie) { return view('admin.movie-form',['movie'=>$movie,'genres'=>Genre::orderBy('name')->get(),'people'=>Person::orderBy('name')->get()]); }
    public function storeMovie(Request $request) { $this->saveMovie($request,new Movie);return redirect()->route('admin.movies')->with('success','Movie created.'); }
    public function updateMovie(Request $request,Movie $movie) { $this->saveMovie($request,$movie);return redirect()->route('admin.movies')->with('success','Movie updated.'); }
    private function saveMovie(Request $request,Movie $movie): void {
        $data=$request->validate([
            'tmdb_id'=>['required','integer','min:1',Rule::unique('movies','tmdb_id')->ignore($movie->id)],
            'title'=>'required|string|max:255','overview'=>'nullable|string|max:10000','release_date'=>'nullable|date_format:Y-m-d',
            'runtime'=>'nullable|integer|min:1|max:600','vote_average'=>'nullable|numeric|min:0|max:10',
            'genres'=>'nullable|array|max:30','genres.*'=>'integer|distinct|exists:genres,id',
            'actors'=>'nullable|array|max:50','actors.*'=>'integer|distinct|exists:people,id',
            'directors'=>'nullable|array|max:10','directors.*'=>'integer|distinct|exists:people,id'
        ]);
        DB::transaction(function() use($movie,$data) {
            $movie->fill(collect($data)->except(['genres','actors','directors'])->all())->save();
            $movie->genres()->sync($data['genres']??[]);
            // Preserve unrelated crew roles; replace only editable cast/director credits.
            $movie->credits()->where(fn($q)=>$q->where('credit_type','cast')->orWhere(fn($q)=>$q->where('credit_type','crew')->where('job','Director')))->delete();
            foreach($data['actors']??[] as $order=>$id) $movie->credits()->create(['person_id'=>$id,'credit_type'=>'cast','cast_order'=>$order]);
            foreach($data['directors']??[] as $id) $movie->credits()->create(['person_id'=>$id,'credit_type'=>'crew','job'=>'Director','department'=>'Directing']);
        });
    }
    public function deleteMovie(Movie $movie) { $movie->delete();return back()->with('success','Movie and dependent records deleted.'); }
    public function activity() { return view('admin.activity',['activities'=>Activity::with('user')->latest()->paginate(30)]); }
}
