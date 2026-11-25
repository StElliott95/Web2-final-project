<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration
{
   public function up(): void
   {
       // Task 9: each user controls what appears on his public profile
       Schema::table('users', function (Blueprint $table) {
           $table->boolean('show_reviews')->default(true);
           $table->boolean('show_lists')->default(true);
           $table->boolean('show_watched')->default(true);
       });
   }
   public function down(): void
   {
       Schema::table('users', function (Blueprint $table) {
           $table->dropColumn(['show_reviews', 'show_lists', 'show_watched']);
       });
   }
};