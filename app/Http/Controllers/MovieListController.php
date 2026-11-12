<?php
namespace App\Http\Controllers;
use App\Models\{Movie,MovieList};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
class MovieListController extends Controller {
    public function index(Request $r) { return view('lists.index',['lists'=>MovieList::where('user_id', $r->user()->id)->withCount('movies')->latest()->paginate(12),'public'=>false]); }
    public function browse(Request $r) {
        $r->validate(['q'=>'nullable|string|max:100']);
        return view('lists.index',['lists'=>MovieList::where('is_public',true)->when($r->filled('q'),fn($q)=>$q->where('title','like','%'.$r->input('q').'%'))->with('user')->withCount('movies')->latest()->paginate(12)->withQueryString(),'public'=>true]);
    }
    public function create() { return view('lists.form',['list'=>new MovieList]); }
    public function store(Request $r) {
        $list=MovieList::where('user_id', $r->user()->id)->create(array_merge($this->data($r), ['user_id' => $r->user()->id])); 
        return redirect()->route('lists.show',$list)->with('success','List created. Add your first movie below.');
    }
    public function show(MovieList $list) {
        Gate::authorize('view',$list);
        return view('lists.show',['list'=>$list->load('user'),'movies'=>$list->movies()->paginate(12),'available'=>auth()->id()===$list->user_id ? Movie::whereNotIn('id',$list->movies()->pluck('movies.id'))->orderBy('title')->get(['id','title']) : collect()]);
    }
    public function edit(MovieList $list) { Gate::authorize('update',$list); return view('lists.form',compact('list')); }
    public function update(Request $r,MovieList $list) {
        Gate::authorize('update',$list); $list->update($this->data($r)); 
        return redirect()->route('lists.show',$list)->with('success','List updated.');
    }
    public function destroy(MovieList $list) {
        Gate::authorize('delete',$list);  $list->delete();
        return redirect()->route('lists.index')->with('success','List deleted.');
    }
    public function add(Request $r,MovieList $list) {
        Gate::authorize('update',$list); $data=$r->validate(['movie_id'=>'required|integer|exists:movies,id']);
        $list->movies()->syncWithoutDetaching([$data['movie_id']]); $list->touch(); return back()->with('success','Movie added.');
    }
    public function remove(MovieList $list,Movie $movie) {
        Gate::authorize('update',$list); $list->movies()->detach($movie); $list->touch(); return back()->with('success','Movie removed.');
    }
    private function data(Request $r): array {
        $data=$r->validate(['title'=>'required|string|max:120','description'=>'nullable|string|max:2000','is_public'=>'required|boolean']);
        return $data;
    }
}

