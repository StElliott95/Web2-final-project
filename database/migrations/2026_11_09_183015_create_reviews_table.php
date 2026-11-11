<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration
{
   /**
    * Run the migrations.
    */
   public function up(): void
   {
       Schema::create('reviews', function (Blueprint $table) {
           $table->id();
           // foreign keys (method 3 of the doctor)
           $table->foreignId('user_id')->constrained()->cascadeOnDelete();
           $table->foreignId('movie_id')->constrained()->cascadeOnDelete();
           $table->integer('rating');                     // 1 .. 10
           $table->longText('body')->nullable();          // review text
           $table->boolean('is_hidden')->default(false);  // admin moderation
           $table->timestamps();
           // each user can review a movie only once
           $table->unique(['user_id', 'movie_id']);
       });
   }
   /**
    * Reverse the migrations.
    */
   public function down(): void
   {
       Schema::dropIfExists('reviews');
   }
};