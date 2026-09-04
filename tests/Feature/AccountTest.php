<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AccountTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_update_profile_information(): void
    {
        $user = User::factory()->create(['type' => 'player']);

        $this->actingAs($user)->patch(route('account.update'), [
            'name' => 'Jace Beleren',
            'email' => 'jace@example.com',
            'type' => 'organizer',
        ])->assertRedirect();

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'Jace Beleren',
            'email' => 'jace@example.com',
            'type' => 'organizer',
        ]);
    }

    public function test_user_can_change_password_with_current_password(): void
    {
        $user = User::factory()->create(['password' => 'old-password']);

        $this->actingAs($user)->put(route('account.password'), [
            'current_password' => 'old-password',
            'password' => 'new-secure-password',
            'password_confirmation' => 'new-secure-password',
        ])->assertRedirect();

        $this->assertTrue(Hash::check('new-secure-password', $user->fresh()->password));
    }
}
