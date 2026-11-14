<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class MoviePreference extends Model {
    protected $fillable = ['user_id','genres','actors','directors'];
    protected $casts = ['genres'=>'array','actors'=>'array','directors'=>'array'];
}
