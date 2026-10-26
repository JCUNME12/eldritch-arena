<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tournaments', function (Blueprint $t) {
            $t->timestamp('results_started_at')->nullable();
            $t->json('results_state')->nullable();
            $t->unsignedInteger('results_revision')->default(0);
            $t->timestamp('report_published_at')->nullable()->index();
            $t->json('report_snapshot')->nullable();
        });
        Schema::create('tournament_report_audits', function (Blueprint $t) {
            $t->id();
            $t->foreignId('tournament_id')->constrained()->cascadeOnDelete();
            $t->foreignId('actor_id')->nullable()->constrained('users')->nullOnDelete();
            $t->string('action');
            $t->text('reason')->nullable();
            $t->json('details');
            $t->timestamp('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tournament_report_audits');
        Schema::table('tournaments', fn (Blueprint $t) => $t->dropColumn(['results_started_at', 'results_state', 'results_revision', 'report_published_at', 'report_snapshot']));
    }
};
