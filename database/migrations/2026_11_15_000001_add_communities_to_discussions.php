<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create('communities', function(Blueprint $table) {
            $table->id();
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->string('name',120)->unique();
            $table->text('description')->nullable();
            $table->timestamps();
        });
        Schema::create('community_user', function(Blueprint $table) {
            $table->foreignId('community_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
            $table->primary(['community_id','user_id']);
        });
        Schema::table('discussion_rooms', function(Blueprint $table) {
            $table->unsignedBigInteger('movie_id')->nullable()->change();
            $table->foreignId('community_id')->nullable()->constrained()->cascadeOnDelete();
        });
    }
    public function down(): void {
        // Removing communities also removes their general-chat rooms (movie_id is null).
        \Illuminate\Support\Facades\DB::table('communities')->delete();
        Schema::table('discussion_rooms', function(Blueprint $table) {
            $table->dropConstrainedForeignId('community_id');
            $table->unsignedBigInteger('movie_id')->nullable(false)->change();
        });
        Schema::dropIfExists('community_user');
        Schema::dropIfExists('communities');
    }
};
