<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class LegacySqliteImportTest extends TestCase
{
    use RefreshDatabase;

    private string $legacyDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->legacyDatabase = tempnam(sys_get_temp_dir(), 'eldritch-legacy-');
        config(['database.connections.legacy_sqlite.database' => $this->legacyDatabase]);
        DB::purge('legacy_sqlite');

        Schema::connection('legacy_sqlite')->create('users', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('email');
            $table->string('password');
            $table->string('type')->default('player');
            $table->string('avatar_color')->default('#A855F7');
            $table->string('premium_plan')->default('free');
            $table->boolean('premium_active')->default(false);
            $table->timestamp('premium_started_at')->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('remember_token')->nullable();
            $table->timestamps();
        });
    }

    protected function tearDown(): void
    {
        DB::purge('legacy_sqlite');

        if (isset($this->legacyDatabase) && is_file($this->legacyDatabase)) {
            unlink($this->legacyDatabase);
        }

        parent::tearDown();
    }

    public function test_it_imports_users_from_the_legacy_sqlite_database(): void
    {
        DB::connection('legacy_sqlite')->table('users')->insert([
            'id' => 42,
            'name' => 'Usuário legado',
            'email' => 'legado@example.com',
            'password' => bcrypt('secret-password'),
            'type' => 'player',
            'premium_active' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->artisan('eldritch:import-sqlite', [
            '--source' => $this->legacyDatabase,
        ])->assertSuccessful();

        $this->assertDatabaseHas('users', [
            'id' => 42,
            'email' => 'legado@example.com',
        ]);
    }

    public function test_it_refuses_to_overwrite_existing_data_without_replace(): void
    {
        User::factory()->create();

        $this->artisan('eldritch:import-sqlite', [
            '--source' => $this->legacyDatabase,
        ])->assertFailed();
    }
}
