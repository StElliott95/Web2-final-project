<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::table('users',fn(Blueprint $table)=>$table->boolean('is_active')->default(true));
        Schema::create('activities',function(Blueprint $table) {
            $table->id();$table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('action');$table->string('subject')->nullable();$table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('activities');Schema::table('users',fn(Blueprint $table)=>$table->dropColumn('is_active')); }
};
