<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('community_topics', function (Blueprint $table) {
            $table->string('image_path')->nullable()->after('body');
        });

        Schema::table('community_comments', function (Blueprint $table) {
            $table->string('image_path')->nullable()->after('body');
        });
    }

    public function down(): void
    {
        Schema::table('community_comments', function (Blueprint $table) {
            $table->dropColumn('image_path');
        });

        Schema::table('community_topics', function (Blueprint $table) {
            $table->dropColumn('image_path');
        });
    }
};
