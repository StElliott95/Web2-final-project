<?php
namespace App\Observers;
use App\Models\Activity;
use Illuminate\Database\Eloquent\Model;
class ActivityObserver {
    public function created(Model $model): void { $this->record('Created',$model); }
    public function updated(Model $model): void { $this->record('Updated',$model); }
    public function deleted(Model $model): void { $this->record('Deleted',$model); }
    private function record(string $verb,Model $model): void {
        if (!auth()->check()) return;
        Activity::create(['user_id'=>auth()->id(),'action'=>$verb.' '.class_basename($model),'subject'=>'#'.$model->getKey()]);
    }
}
