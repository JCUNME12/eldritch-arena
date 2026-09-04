<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PremiumTest extends TestCase
{
    use RefreshDatabase;

    public function test_demo_subscription_is_disabled_by_default(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('premium.subscribe'), ['plan' => 'player_premium'])
            ->assertNotFound();

        $this->assertFalse($user->fresh()->isPremium());
    }

    public function test_demo_subscription_can_be_enabled_explicitly(): void
    {
        config(['features.demo_premium_subscription' => true]);
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('premium.subscribe'), ['plan' => 'player_premium'])
            ->assertRedirect(route('premium'));

        $this->assertTrue($user->fresh()->isPremium());
    }
}
