<?php

namespace Tests\Feature;

use App\Models\CardListing;
use App\Models\CommunityTopic;
use App\Models\Tournament;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminTest extends TestCase
{
    use RefreshDatabase;

    public function test_only_administrators_can_open_the_admin_panel(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get(route('admin.index'))->assertForbidden();

        $admin = User::factory()->create(['is_admin' => true, 'type' => 'organizer']);

        $this->actingAs($admin)
            ->get(route('admin.index'))
            ->assertOk()
            ->assertSee('Central de operação');
    }

    public function test_admin_can_update_user_permissions(): void
    {
        $admin = User::factory()->create(['is_admin' => true, 'type' => 'organizer']);
        $user = User::factory()->create(['type' => 'player']);

        $this->actingAs($admin)->patch(route('admin.users.update', $user), [
            'type' => 'organizer',
            'is_admin' => '1',
            'premium_active' => '1',
        ])->assertRedirect();

        $user->refresh();
        $this->assertTrue($user->isAdmin());
        $this->assertTrue($user->isOrganizer());
        $this->assertTrue($user->isPremium());
    }

    public function test_admin_cannot_remove_their_own_admin_access(): void
    {
        $admin = User::factory()->create(['is_admin' => true, 'type' => 'organizer']);

        $this->actingAs($admin)
            ->from(route('admin.index'))
            ->patch(route('admin.users.update', $admin), [
                'type' => 'organizer',
                'is_admin' => '0',
                'premium_active' => '0',
            ])
            ->assertRedirect(route('admin.index'))
            ->assertSessionHasErrors('is_admin');

        $this->assertTrue($admin->fresh()->isAdmin());
    }

    public function test_admin_can_manage_tournament_status_and_highlight(): void
    {
        $admin = User::factory()->create(['is_admin' => true, 'type' => 'organizer']);
        $tournament = Tournament::create([
            'organizer_id' => $admin->id,
            'title' => 'Arena Regional',
            'game' => 'Magic: The Gathering',
            'starts_at' => now()->addWeek(),
            'prize' => 'Boosters',
            'entry_fee' => 20,
            'slots' => 32,
            'location' => 'Loja Central',
            'status' => Tournament::STATUS_PUBLISHED,
        ]);

        $this->actingAs($admin)->patch(route('admin.tournaments.update', $tournament), [
            'status' => Tournament::STATUS_CANCELLED,
            'highlighted' => '1',
        ])->assertRedirect();

        $tournament->refresh();
        $this->assertSame(Tournament::STATUS_CANCELLED, $tournament->status);
        $this->assertTrue($tournament->highlighted);
    }

    public function test_admin_can_moderate_marketplace_and_community(): void
    {
        $admin = User::factory()->create(['is_admin' => true, 'type' => 'organizer']);
        $seller = User::factory()->create();
        $listing = CardListing::create([
            'user_id' => $seller->id,
            'name' => 'Black Lotus',
            'game' => 'Magic',
            'rarity' => 'Rara',
            'condition' => 'Excelente',
            'price' => 100,
            'seller_name' => $seller->name,
            'seller_type' => 'player',
            'contact_email' => $seller->email,
        ]);
        $topic = CommunityTopic::create([
            'user_id' => $seller->id,
            'title' => 'Comunicado importante da comunidade',
            'category' => 'Avisos',
            'body' => 'Conteúdo suficiente para validar a moderação administrativa.',
        ]);

        $this->actingAs($admin)->patch(route('admin.topics.update', $topic), [
            'is_pinned' => '1',
        ])->assertRedirect();
        $this->assertTrue($topic->fresh()->is_pinned);

        $this->actingAs($admin)
            ->delete(route('admin.listings.destroy', $listing))
            ->assertRedirect();
        $this->assertDatabaseMissing('card_listings', ['id' => $listing->id]);
    }
}
