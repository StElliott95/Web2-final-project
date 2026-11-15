<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Community extends Model {
    protected $fillable=['name','description','created_by'];
    public function members() { return $this->belongsToMany(User::class)->withTimestamps(); }
    public function rooms() { return $this->hasMany(DiscussionRoom::class); }
}
