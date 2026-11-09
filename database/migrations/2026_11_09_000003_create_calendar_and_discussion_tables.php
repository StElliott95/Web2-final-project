<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('calendar_connections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('provider', 50);
            $table->string('provider_account_id', 128)->nullable();
            $table->string('calendar_id', 128)->nullable();
            $table->text('access_token')->nullable();
            $table->text('refresh_token')->nullable();
            $table->timestamp('token_expires_at')->nullable();
            $table->string('connection_status', 20)->default('connected');
            $table->timestamps();

            $table->unique(
                ['user_id', 'provider', 'provider_account_id'],
                'calendar_connections_user_provider_account_unique'
            );
        });

        Schema::create('calendar_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('calendar_connection_id')
                ->constrained('calendar_connections')
                ->cascadeOnDelete();
            $table->foreignId('movie_id')->nullable()
                ->constrained('movies')
                ->nullOnDelete();
            $table->string('external_event_id', 128)->nullable();
            $table->string('event_title');
            $table->dateTime('starts_at');
            $table->dateTime('ends_at')->nullable();
            $table->string('sync_status', 20)->default('pending');
            $table->timestamp('synced_at')->nullable();
            $table->timestamps();

            $table->unique(
                ['calendar_connection_id', 'external_event_id'],
                'calendar_events_connection_external_id_unique'
            );
            $table->index(['movie_id', 'starts_at'], 'calendar_events_movie_start_index');
        });

        Schema::create('discussion_rooms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('movie_id')->constrained()->cascadeOnDelete();
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->string('title', 150)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['movie_id', 'is_active'], 'discussion_rooms_movie_active_index');
        });

        Schema::create('discussion_room_members', function (Blueprint $table) {
            $table->foreignId('room_id')->constrained('discussion_rooms')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->timestamp('joined_at')->useCurrent();
            $table->timestamp('left_at')->nullable();
            $table->timestamps();

            $table->primary(['room_id', 'user_id']);
            $table->index('user_id');
        });

        Schema::create('discussion_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('room_id')->constrained('discussion_rooms')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->text('message');
            $table->timestamps();

            $table->index(['room_id', 'created_at'], 'discussion_messages_room_created_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('discussion_messages');
        Schema::dropIfExists('discussion_room_members');
        Schema::dropIfExists('discussion_rooms');
        Schema::dropIfExists('calendar_events');
        Schema::dropIfExists('calendar_connections');
    }
};
