<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tournaments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organizer_id')->constrained('users')->cascadeOnDelete();
            $table->string('title');
            $table->string('game');
            $table->dateTime('starts_at');
            $table->string('prize');
            $table->decimal('entry_fee', 10, 2)->default(0);
            $table->integer('slots')->default(32);
            $table->string('location')->default('Arena local');
            $table->text('description')->nullable();
            $table->boolean('highlighted')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tournaments');
    }
};
