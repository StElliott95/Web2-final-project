<?php
namespace App\Http\Controllers;
use App\Models\{Community,DiscussionRoom,Movie};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
class CommunityController extends Controller {
    public function index(Request $request) {
        $request->validate(['q'=>'nullable|string|max:100','page'=>'sometimes|integer|min:1']);
        $communities=Community::query()->when($request->filled('q'),fn($q)=>$q->where('name','like','%'.$request->input('q').'%'))
            ->withCount('members')->withCount(['rooms as threads_count'=>fn($q)=>$q->whereNotNull('movie_id')->where('is_active',true)])
            ->orderBy('name')->orderBy('id')->paginate(12)->withQueryString();
        return view('communities.index',compact('communities'));
    }
    public function create() { return view('communities.create'); }
    public function store(Request $request) {
        $data=$request->validate(['name'=>'required|string|max:120|unique:communities,name','description'=>'nullable|string|max:2000']);
        $community=DB::transaction(function() use($request,$data) {
            $community=Community::create($data+['created_by'=>$request->user()->id]);
            $community->members()->attach($request->user()->id);
            $community->rooms()->create(['created_by'=>$request->user()->id,'title'=>'General chat','is_active'=>true]);
            return $community;
        });
        return redirect()->route('communities.show',$community)->with('success','Community created. You can start chatting or open a movie thread.');
    }
    public function show(Request $request,Community $community) {
        $request->validate(['page'=>'sometimes|integer|min:1']);
        $joined=$request->user() && $community->members()->where('users.id',$request->user()->id)->exists();
        $threads=$community->rooms()->whereNotNull('movie_id')->where('is_active',true)->with('movie')->withCount('messages')->latest('id')->paginate(12);
        $chat=$community->rooms()->whereNull('movie_id')->where('is_active',true)->first();
        $movies=$joined ? Movie::where('adult',false)->orderBy('title')->get(['id','title']) : collect();
        return view('communities.show',compact('community','joined','threads','chat','movies'));
    }
    public function join(Request $request,Community $community) {
        $community->members()->syncWithoutDetaching([$request->user()->id]);
        return redirect()->route('communities.show',$community)->with('success','You joined the community.');
    }
    public function thread(Request $request,Community $community) {
        $this->ensureMember($request,$community);
        $data=$request->validate(['title'=>'required|string|max:150','movie_id'=>'required|integer|exists:movies,id']);
        abort_if(Movie::findOrFail($data['movie_id'])->adult,422,'Choose a movie from the available catalogue.');
        $room=$community->rooms()->create($data+['created_by'=>$request->user()->id,'is_active'=>true]);
        return redirect()->route('communities.room',[$community,$room]);
    }
    public function room(Request $request,Community $community,DiscussionRoom $room) {
        abort_unless($room->community_id === $community->id && $room->is_active,404);
        $this->ensureMember($request,$community);
        $room->members()->syncWithoutDetaching([$request->user()->id=>['joined_at'=>now(),'left_at'=>null]]);
        return app(DiscussionRoomController::class)->renderRoom($request,$room);
    }
    private function ensureMember(Request $request,Community $community): void {
        abort_unless($community->members()->where('users.id',$request->user()->id)->exists(),403,'Join this community first.');
    }
}
