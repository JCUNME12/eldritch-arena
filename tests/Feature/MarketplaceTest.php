<?php

namespace Tests\Feature;

use App\Models\CardListing;
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

    public function test_owner_can_update_and_remove_their_listing(): void
    {
        $user = User::factory()->create(['type' => 'player']);
        $listing = CardListing::create([
            'user_id' => $user->id,
            'name' => 'Pikachu',
            'game' => 'Pokémon',
            'rarity' => 'Rara',
            'condition' => 'Bom',
            'price' => 20,
            'seller_name' => $user->name,
            'seller_type' => 'player',
            'contact_email' => $user->email,
        ]);

        $this->actingAs($user)->put(route('marketplace.update', $listing), [
            'name' => 'Pikachu Promo',
            'game' => 'Pokémon',
            'edition' => 'Promo',
            'rarity' => 'Ultra Rara',
            'condition' => 'Excelente',
            'description' => 'Carta original protegida em sleeve.',
            'price' => 80,
            'image_url' => '',
        ])->assertRedirect(route('marketplace.show', $listing));

        $this->assertDatabaseHas('card_listings', ['id' => $listing->id, 'name' => 'Pikachu Promo']);

        $this->actingAs($user)->delete(route('marketplace.destroy', $listing))->assertRedirect(route('marketplace'));
        $this->assertDatabaseMissing('card_listings', ['id' => $listing->id]);
    }

    public function test_user_cannot_edit_another_users_listing(): void
    {
        $owner = User::factory()->create();
        $stranger = User::factory()->create();
        $listing = CardListing::create([
            'user_id' => $owner->id,
            'name' => 'Sol Ring',
            'game' => 'Magic',
            'rarity' => 'Incomum',
            'condition' => 'Bom',
            'price' => 10,
            'seller_name' => $owner->name,
            'seller_type' => 'player',
            'contact_email' => $owner->email,
        ]);

        $this->actingAs($stranger)->get(route('marketplace.edit', $listing))->assertForbidden();
    }
}
