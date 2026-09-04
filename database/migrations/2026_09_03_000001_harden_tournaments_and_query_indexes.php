<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tournaments', function (Blueprint $table) {
            $table->string('status', 20)->default('published');
            $table->index(['status', 'starts_at']);
            $table->index(['highlighted', 'starts_at']);
        });

        Schema::table('card_listings', function (Blueprint $table) {
            $table->index(['game', 'highlighted']);
        });

        Schema::table('community_topics', function (Blueprint $table) {
            $table->index(['is_pinned', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::table('community_topics', function (Blueprint $table) {
            $table->dropIndex(['is_pinned', 'created_at']);
        });

        Schema::table('card_listings', function (Blueprint $table) {
            $table->dropIndex(['game', 'highlighted']);
        });

        Schema::table('tournaments', function (Blueprint $table) {
            $table->dropIndex(['status', 'starts_at']);
            $table->dropIndex(['highlighted', 'starts_at']);
            $table->dropColumn('status');
        });
    }
};
