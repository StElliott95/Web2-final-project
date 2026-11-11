<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration
{
   public function up(): void
   {
       // Task 5: personal watchlist
       Schema::create('watchlist_items', function (Blueprint $table) {
           $table->id();
           $table->foreignId('user_id')->constrained()->cascadeOnDelete();
           $table->foreignId('movie_id')->constrained()->cascadeOnDelete();
           $table->boolean('is_watched')->default(false);
           $table->timestamp('watched_at')->nullable();
           $table->timestamps();
           // the same movie only once in my watchlist
           $table->unique(['user_id', 'movie_id']);
       });
   }
   public function down(): void
   {
       Schema::dropIfExists('watchlist_items');
   }
};