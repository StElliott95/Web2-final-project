<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const COLUMNS = [
        'sentiment_label',
        'sentiment_score',
        'sentiment_analyzed_at',
    ];

    public function up(): void
    {
        if (! Schema::hasTable('reviews')) {
            return;
        }

        $columns = array_values(array_filter(
            self::COLUMNS,
            fn (string $column): bool => Schema::hasColumn('reviews', $column)
        ));

        if ($columns === []) {
            return;
        }

        Schema::table('reviews', function (Blueprint $table) use ($columns): void {
            $table->dropColumn($columns);
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('reviews')) {
            return;
        }

        $missing = array_values(array_filter(
            self::COLUMNS,
            fn (string $column): bool => ! Schema::hasColumn('reviews', $column)
        ));

        if ($missing === []) {
            return;
        }

        Schema::table('reviews', function (Blueprint $table) use ($missing): void {
            if (in_array('sentiment_label', $missing, true)) {
                $table->string('sentiment_label', 20)->nullable();
            }

            if (in_array('sentiment_score', $missing, true)) {
                $table->decimal('sentiment_score', 5, 4)->nullable();
            }

            if (in_array('sentiment_analyzed_at', $missing, true)) {
                $table->timestamp('sentiment_analyzed_at')->nullable();
            }
        });
    }
};
