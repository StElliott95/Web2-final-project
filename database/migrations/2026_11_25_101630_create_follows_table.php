<?php

use Illuminate\Database\Migrations\Migration;

use Illuminate\Database\Schema\Blueprint;

use Illuminate\Support\Facades\Schema;

return new class extends Migration

{

    public function up(): void

    {

        // Task 9: many to many between users and users (pivot table)

        Schema::create('follows', function (Blueprint $table) {

            $table->id();

            // the user who follows

            $table->foreignId('follower_id')->references('id')->on('users')->cascadeOnDelete();

            // the user who is followed

            $table->foreignId('following_id')->references('id')->on('users')->cascadeOnDelete();

            $table->timestamps();

            // I can follow the same person only once

            $table->unique(['follower_id', 'following_id']);

        });

    }

    public function down(): void

    {

        Schema::dropIfExists('follows');

    }

};
 