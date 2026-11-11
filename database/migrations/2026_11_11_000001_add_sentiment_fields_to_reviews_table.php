<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reviews', function (Blueprint $table) {
            $table->string('sentiment_label', 20)->nullable()->after('is_hidden');
            $table->decimal('sentiment_score', 5, 4)->nullable()->after('sentiment_label');
            $table->timestamp('sentiment_analyzed_at')->nullable()->after('sentiment_score');
        });
    }

    public function down(): void
    {
        Schema::table('reviews', function (Blueprint $table) {
            $table->dropColumn([
                'sentiment_label',
                'sentiment_score',
                'sentiment_analyzed_at',
            ]);
        });
    }
};
