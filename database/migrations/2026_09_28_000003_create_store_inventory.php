<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', fn (Blueprint $t) => $t->boolean('is_admin')->default(false));
        Schema::create('stores', function (Blueprint $t) {
            $t->id();
            $t->foreignId('user_id')->unique()->constrained()->restrictOnDelete();
            $t->string('name', 120);
            $t->string('contact_email');
            $t->text('description')->nullable();
            $t->timestamps();
        });
        Schema::create('inventory_items', function (Blueprint $t) {
            $t->id();
            $t->foreignId('store_id')->constrained()->restrictOnDelete();
            $t->string('sku', 60);
            $t->string('name', 120);
            $t->string('game');
            $t->string('edition', 120)->nullable();
            $t->string('rarity');
            $t->string('condition');
            $t->text('description')->nullable();
            $t->string('image_url', 500)->nullable();
            $t->decimal('cost', 10, 2)->default(0);
            $t->decimal('price', 10, 2);
            $t->unsignedInteger('quantity')->default(0);
            $t->unsignedInteger('minimum_quantity')->default(2);
            $t->boolean('published')->default(false);
            $t->boolean('archived')->default(false);
            $t->timestamps();
            $t->unique(['store_id', 'sku']);
        });
        Schema::create('stock_movements', function (Blueprint $t) {
            $t->id();
            $t->foreignId('inventory_item_id')->constrained()->restrictOnDelete();
            $t->foreignId('user_id')->constrained()->restrictOnDelete();
            $t->uuid('request_id')->unique();
            $t->integer('delta');
            $t->unsignedInteger('before_quantity');
            $t->unsignedInteger('after_quantity');
            $t->string('reason', 255);
            $t->timestamps();
        });
        Schema::table('card_listings', fn (Blueprint $t) => $t->foreignId('inventory_item_id')->nullable()->unique()->constrained()->restrictOnDelete());
        Schema::create('admin_access_logs', function (Blueprint $t) {
            $t->id();
            $t->foreignId('user_id')->constrained()->restrictOnDelete();
            $t->boolean('granted');
            $t->string('source')->default('console');
            $t->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('admin_access_logs');
        Schema::table('card_listings', fn (Blueprint $t) => $t->dropConstrainedForeignId('inventory_item_id'));
        Schema::dropIfExists('stock_movements');
        Schema::dropIfExists('inventory_items');
        Schema::dropIfExists('stores');
        Schema::table('users', fn (Blueprint $t) => $t->dropColumn('is_admin'));
    }
};
