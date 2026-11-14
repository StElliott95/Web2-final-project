<?php
namespace App\Http\Controllers;
use App\Models\{Genre,Person,MoviePreference};
use App\Services\RecommendationService;
use Illuminate\Http\Request;
class RecommendationController extends Controller {
    public function index(Request $request,RecommendationService $service) {
        return view('recommendations.index',['recommendations'=>$service->forUser($request->user())]);
    }
    public function edit(Request $request) {
        return view('recommendations.preferences',[
            'preferences'=>MoviePreference::where('user_id',$request->user()->id)->first(),
            'choices'=>[
                'genres'=>Genre::orderBy('name')->get(),
                'actors'=>Person::whereHas('movieCredits',fn($q)=>$q->where('credit_type','cast'))->orderBy('name')->get(),
                'directors'=>Person::whereHas('movieCredits',fn($q)=>$q->where('credit_type','crew')->where('job','Director'))->orderBy('name')->get()
            ]
        ]);
    }
    public function update(Request $request) {
        $data=$request->validate([
            'genres'=>'nullable|array|max:30','genres.*'=>'integer|distinct|exists:genres,id',
            'actors'=>'nullable|array|max:30','actors.*'=>'integer|distinct|exists:people,id',
            'directors'=>'nullable|array|max:30','directors.*'=>'integer|distinct|exists:people,id'
        ]);
        foreach (['genres','actors','directors'] as $key) $data[$key]=array_map('intval',$data[$key]??[]);
        MoviePreference::updateOrCreate(['user_id'=>$request->user()->id],$data);
        return redirect()->route('recommendations')->with('success','Preferences saved. Your recommendations have been updated.');
    }
}
