<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('movies', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tmdb_id')->unique();
            $table->string('title');
            $table->string('original_title')->nullable();
            $table->text('overview')->nullable();
            $table->string('poster_path')->nullable();
            $table->string('backdrop_path')->nullable();
            $table->date('release_date')->nullable();
            $table->unsignedSmallInteger('runtime')->nullable();
            $table->string('original_language', 10)->nullable();
            $table->decimal('vote_average', 4, 2)->nullable();
            $table->unsignedInteger('vote_count')->default(0);
            $table->decimal('popularity', 10, 3)->nullable();
            $table->boolean('adult')->default(false);
            $table->timestamp('last_synced_at')->nullable();
            $table->timestamps();

            $table->fullText(['title', 'original_title', 'overview'], 'movies_title_overview_fulltext');
            $table->index(['release_date', 'title'], 'movies_release_title_index');
            $table->index(['vote_average', 'release_date'], 'movies_rating_release_index');
            $table->index(['popularity', 'release_date'], 'movies_popularity_release_index');
        });

        Schema::create('genres', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('tmdb_id')->unique();
            $table->string('name', 100);
            $table->timestamps();
        });

        Schema::create('movie_genre', function (Blueprint $table) {
            $table->foreignId('movie_id')->constrained()->cascadeOnDelete();
            $table->foreignId('genre_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
            $table->primary(['movie_id', 'genre_id']);
            $table->index('genre_id');
        });

        Schema::create('people', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tmdb_person_id')->unique();
            $table->string('name');
            $table->string('profile_path')->nullable();
            $table->string('known_for_department', 100)->nullable();
            $table->timestamps();
        });

        Schema::create('movie_credits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('movie_id')->constrained()->cascadeOnDelete();
            $table->foreignId('person_id')->constrained()->cascadeOnDelete();
            $table->enum('credit_type', ['cast', 'crew']);
            $table->string('department', 100)->nullable();
            $table->string('job', 100)->nullable();
            $table->string('character_name')->nullable();
            $table->unsignedSmallInteger('cast_order')->nullable();
            $table->timestamps();

            $table->index(['movie_id', 'credit_type', 'cast_order'], 'movie_credits_cast_order_index');
            $table->index(['person_id', 'credit_type'], 'movie_credits_person_type_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('movie_credits');
        Schema::dropIfExists('people');
        Schema::dropIfExists('movie_genre');
        Schema::dropIfExists('genres');
        Schema::dropIfExists('movies');
    }
};
