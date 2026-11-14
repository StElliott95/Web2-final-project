<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create('movie_preferences',function(Blueprint $table) {
            $table->id(); $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->json('genres'); $table->json('actors'); $table->json('directors'); $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('movie_preferences'); }
};
