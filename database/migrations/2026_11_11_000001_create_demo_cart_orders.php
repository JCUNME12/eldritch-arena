<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('demo_cart_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->restrictOnDelete();
            $table->uuid('request_id')->unique();
            $table->string('method');
            $table->string('status')->default('pending');
            $table->timestamps();
        });
        Schema::table('demo_payments', fn (Blueprint $table) => $table->foreignId('cart_order_id')->nullable()->constrained('demo_cart_orders')->restrictOnDelete());
    }

    public function down(): void
    {
        Schema::table('demo_payments', fn (Blueprint $table) => $table->dropConstrainedForeignId('cart_order_id'));
        Schema::dropIfExists('demo_cart_orders');
    }
};
