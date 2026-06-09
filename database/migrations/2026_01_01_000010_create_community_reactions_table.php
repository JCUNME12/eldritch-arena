<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('community_reactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('community_topic_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('type', 20);
            $table->timestamps();

            $table->unique(['community_topic_id', 'user_id', 'type'], 'community_reaction_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('community_reactions');
    }
};
