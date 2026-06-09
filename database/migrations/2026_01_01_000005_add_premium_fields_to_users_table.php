<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('premium_plan')->default('free')->after('avatar_color');
            $table->boolean('premium_active')->default(false)->after('premium_plan');
            $table->timestamp('premium_started_at')->nullable()->after('premium_active');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['premium_plan', 'premium_active', 'premium_started_at']);
        });
    }
};
