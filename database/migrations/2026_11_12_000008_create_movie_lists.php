<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create('movie_lists',function(Blueprint $table) {
            $table->id(); $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('title',120); $table->text('description')->nullable(); $table->boolean('is_public')->default(false)->index(); $table->timestamps();
        });
        Schema::create('movie_list_movie',function(Blueprint $table) {
            $table->id(); $table->foreignId('movie_list_id')->constrained()->cascadeOnDelete();
            $table->foreignId('movie_id')->constrained()->cascadeOnDelete(); $table->timestamps(); $table->unique(['movie_list_id','movie_id']);
        });
    }
    public function down(): void { Schema::dropIfExists('movie_list_movie'); Schema::dropIfExists('movie_lists'); }
};
