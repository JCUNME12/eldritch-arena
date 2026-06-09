<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('card_listings', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->after('id')->constrained()->nullOnDelete();
            $table->string('edition')->nullable()->after('game');
            $table->text('description')->nullable()->after('condition');
            $table->string('seller_type')->default('player')->after('seller_name');
            $table->string('contact_email')->nullable()->after('seller_type');
        });
    }

    public function down(): void
    {
        Schema::table('card_listings', function (Blueprint $table) {
            $table->dropConstrainedForeignId('user_id');
            $table->dropColumn(['edition', 'description', 'seller_type', 'contact_email']);
        });
    }
};
