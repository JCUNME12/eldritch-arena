<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('stores', function (Blueprint $t) {
            $t->boolean('is_demo')->default(false);
            $t->foreignId('demo_presenter_id')->nullable()->constrained('users')->restrictOnDelete();
        });
        Schema::create('demo_payments', function (Blueprint $t) {
            $t->id();
            $t->foreignId('store_id')->constrained()->restrictOnDelete();
            $t->foreignId('user_id')->constrained()->restrictOnDelete();
            $t->foreignId('inventory_item_id')->nullable()->constrained()->restrictOnDelete();
            $t->uuid('request_id')->unique();
            $t->string('purpose');
            $t->string('method');
            $t->string('status')->default('pending');
            $t->string('description');
            $t->unsignedInteger('quantity')->default(1);
            $t->unsignedBigInteger('amount_cents');
            $t->unsignedBigInteger('cost_cents')->default(0);
            $t->unsignedBigInteger('fee_cents')->default(0);
            $t->unsignedInteger('fee_basis_points')->default(0);
            $t->json('events');
            $t->timestamps();
            $t->index(['store_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('demo_payments');
        Schema::table('stores', function (Blueprint $t) {
            $t->dropConstrainedForeignId('demo_presenter_id');
            $t->dropColumn('is_demo');
        });
    }
};
