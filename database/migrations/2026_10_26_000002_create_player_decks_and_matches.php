<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('player_decks', function (Blueprint $t) {
            $t->id();
            $t->foreignId('user_id')->constrained()->restrictOnDelete();
            $t->string('name', 100);
            $t->string('game', 60);
            $t->string('format', 60);
            $t->string('archetype', 100)->nullable();
            $t->text('notes')->nullable();
            $t->boolean('archived')->default(false);
            $t->timestamps();
        });
        Schema::create('player_matches', function (Blueprint $t) {
            $t->id();
            $t->foreignId('player_deck_id')->constrained()->restrictOnDelete();
            $t->uuid('request_id')->unique();
            $t->string('opponent', 100);
            $t->string('opponent_key', 100);
            $t->string('result', 10);
            $t->date('played_on');
            $t->unsignedSmallInteger('players')->default(2);
            $t->string('context', 20)->default('casual');
            $t->text('notes')->nullable();
            $t->timestamps();
            $t->softDeletes();
            $t->index(['player_deck_id', 'played_on']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('player_matches');
        Schema::dropIfExists('player_decks');
    }
};
