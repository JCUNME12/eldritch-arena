<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MarketplaceTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_publish_a_card_listing(): void
    {
        $user = User::factory()->create([
            'name' => 'Nissa',
            'email' => 'nissa@example.com',
            'type' => 'player',
            'premium_active' => false,
        ]);

        $response = $this->actingAs($user)->post(route('marketplace.store'), [
            'name' => 'Black Lotus',
            'game' => 'Magic',
            'edition' => 'Alpha',
            'rarity' => 'Rara',
            'condition' => 'Excelente',
            'description' => 'Carta protegida e muito bem conservada.',
            'price' => 999999.99,
            'image_url' => 'https://example.com/card.webp',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('card_listings', [
            'user_id' => $user->id,
            'name' => 'Black Lotus',
            'seller_name' => 'Nissa',
            'contact_email' => 'nissa@example.com',
            'highlighted' => false,
        ]);
    }

    public function test_marketplace_rejects_unknown_catalog_values(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post(route('marketplace.store'), [
            'name' => 'Carta inválida',
            'game' => 'Jogo inexistente',
            'rarity' => 'Impossível',
            'condition' => 'Teletransportada',
            'price' => 10,
        ])->assertSessionHasErrors(['game', 'rarity', 'condition']);
    }
}
