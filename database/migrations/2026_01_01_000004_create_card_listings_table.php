<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('card_listings', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('game');
            $table->string('rarity');
            $table->string('condition');
            $table->decimal('price', 10, 2);
            $table->string('image_url')->nullable();
            $table->string('seller_name');
            $table->boolean('highlighted')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('card_listings');
    }
};
