<?php

namespace Tests\Feature;

use App\Models\CardListing;
use App\Models\Tournament;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ArenaFlowsTest extends TestCase
{
    use RefreshDatabase;

    private function tournament(array $overrides = []): Tournament
    {
        return Tournament::create(array_merge([
            'organizer_id' => User::factory()->create(['type' => 'organizer'])->id,
            'title' => 'Encontro Commander', 'game' => 'Magic: The Gathering', 'format' => 'Commander',
            'starts_at' => now()->addDays(5), 'slots' => 2, 'entry_fee' => 0,
            'location' => 'Loja Arena', 'prize' => 'Participação',
        ], $overrides));
    }

    public function test_registration_is_idempotent_and_capacity_is_enforced(): void
    {
        $t = $this->tournament();
        $url = route('tournaments.register', $t);
        $first = User::factory()->create();
        $this->actingAs($first)->post($url)->assertSessionHasNoErrors();
        $this->post($url)->assertSessionHasNoErrors();
        $this->assertSame(1, $t->registrations()->count());
        $this->actingAs(User::factory()->create())->post($url)->assertSessionHasNoErrors();
        $this->actingAs(User::factory()->create())->post($url)->assertSessionHasErrors('tournament');
        $this->assertSame(2, $t->registrations()->count());
        $this->actingAs($first)->delete(route('tournaments.unregister', $t))->assertRedirect();
        $this->assertSame(1, $t->registrations()->count());
    }

    public function test_closed_and_cancelled_events_reject_registrations(): void
    {
        $this->actingAs(User::factory()->create());
        foreach ([['starts_at' => now()->subDay()], ['cancelled_at' => now()]] as $data) {
            $t = $this->tournament($data);
            $this->post(route('tournaments.register', $t))->assertSessionHasErrors('tournament');
            $this->assertSame(0, $t->registrations()->count());
        }
    }

    public function test_only_owner_can_manage_events_and_cannot_register_in_own_event(): void
    {
        $t = $this->tournament();
        $this->actingAs(User::factory()->create())->get(route('tournaments.edit', $t))->assertForbidden();
        $this->patch(route('tournaments.cancel', $t))->assertForbidden();
        $this->patch(route('tournaments.update', $t), [])->assertForbidden();
        $this->actingAs($t->organizer)->post(route('tournaments.register', $t))->assertSessionHasErrors('tournament');
        $this->get(route('tournaments.edit', $t))->assertOk();
        $this->patch(route('tournaments.cancel', $t))->assertRedirect();
        $this->assertNotNull($t->fresh()->cancelled_at);
    }

    public function test_formats_dates_and_edit_capacity_are_validated(): void
    {
        $t = $this->tournament();
        $data = ['title' => 'Evento revisado', 'game' => $t->game, 'format' => 'Commander',
            'starts_at' => now()->addWeek()->format('Y-m-d H:i'), 'slots' => 3,
            'entry_fee' => 0, 'prize' => 'Cartas', 'location' => 'Loja'];
        $this->actingAs($t->organizer);
        $this->post('/torneios', array_merge($data, ['format' => 'Speed Duel']))->assertSessionHasErrors('format');
        $this->post('/torneios', array_merge($data, ['starts_at' => '2020-01-01']))->assertSessionHasErrors('starts_at');
        $this->patch(route('tournaments.update', $t), $data)->assertSessionHasNoErrors()->assertRedirect();
        $this->assertSame('Evento revisado', $t->fresh()->title);
        foreach (User::factory()->count(3)->create() as $user) {
            $t->registrations()->create(['user_id' => $user->id, 'status' => 'confirmed']);
        }
        $this->patch(route('tournaments.update', $t), array_merge($data, ['slots' => 2]))->assertSessionHasErrors('slots');
    }

    public function test_marketplace_full_lifecycle_and_owner_protection(): void
    {
        $owner = User::factory()->create();
        $data = ['name' => 'Carta Aurora', 'game' => 'Magic', 'rarity' => 'Rara', 'condition' => 'Bom', 'price' => 25];
        $this->actingAs($owner)->post('/marketplace', $data)->assertSessionHasNoErrors()->assertRedirect();
        $card = CardListing::sole();
        $this->get(route('marketplace.edit', $card))->assertOk();
        $this->get('/marketplace?q=Aurora&mine=1&sort=price_asc')->assertOk()->assertSee('Carta Aurora');
        $this->get('/marketplace?q=Inexistente')->assertOk()->assertDontSee('Carta Aurora');
        $this->actingAs(User::factory()->create())->patch(route('marketplace.update', $card), $data)->assertForbidden();
        $this->delete(route('marketplace.destroy', $card))->assertForbidden();
        $this->actingAs($owner)->patch(route('marketplace.update', $card), array_merge($data, ['price' => 30]))->assertSessionHasNoErrors();
        $this->assertEquals(30, $card->fresh()->price);
        $this->delete(route('marketplace.destroy', $card))->assertRedirect(route('marketplace'));
        $this->assertDatabaseMissing('card_listings', ['id' => $card->id]);
    }

    public function test_plus_can_be_activated_and_cancelled_without_payment(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user)->post(route('premium.subscribe'), ['plan' => 'player_premium'])->assertRedirect();
        $this->assertTrue($user->fresh()->isPremium());
        $this->delete(route('premium.cancel'))->assertRedirect();
        $this->assertFalse($user->fresh()->isPremium());
    }

    public function test_counter_is_public_and_login_does_not_publish_seed_credentials(): void
    {
        $this->get('/contador-de-vida')->assertOk()->assertSee('Marcador de vida');
        $this->get('/login')->assertDontSee('jogador@eldritch.test')->assertDontSee('Jogador demo');
    }

    public function test_event_time_is_converted_from_brasilia_to_application_timezone(): void
    {
        config(['app.timezone' => 'America/Rio_Branco']);
        $this->actingAs(User::factory()->create())->post('/torneios', [
            'title' => 'Fuso horário', 'game' => 'Magic', 'format' => 'Commander',
            'starts_at' => '2027-03-20T18:00', 'slots' => 16, 'entry_fee' => 0,
            'prize' => 'Cartas', 'location' => 'Loja',
        ])->assertSessionHasNoErrors();
        $this->assertDatabaseHas('tournaments', ['starts_at' => '2027-03-20 16:00:00']);
    }

    public function test_malformed_game_input_returns_validation_errors_instead_of_server_error(): void
    {
        $this->actingAs(User::factory()->create())->post('/torneios', [
            'game' => ['Magic'], 'format' => ['Commander'],
        ])->assertSessionHasErrors(['game', 'format']);
        $this->assertDatabaseCount('tournaments', 0);
    }

    public function test_new_magic_formats_can_be_used_for_events_but_not_other_games(): void
    {
        $this->actingAs(User::factory()->create());
        foreach (['Vintage', 'Gigante de Duas Cabeças', 'Archenemy · Commander', 'Oathbreaker', 'Conspiracy', 'Pick-Two Draft', 'Booster Draft por Equipes'] as $format) {
            $this->post('/torneios', [
                'title' => 'Mesa '.$format, 'game' => 'Magic', 'format' => $format,
                'starts_at' => '2027-03-20T18:00', 'slots' => 16, 'entry_fee' => 0,
                'prize' => 'Cartas', 'location' => 'Loja',
            ])->assertSessionHasNoErrors();
            $this->assertDatabaseHas('tournaments', ['format' => $format]);
        }
        $this->post('/torneios', [
            'title' => 'Formato incorreto', 'game' => 'Pokémon', 'format' => 'Oathbreaker',
            'starts_at' => '2027-03-20T18:00', 'slots' => 16, 'entry_fee' => 0,
            'prize' => 'Cartas', 'location' => 'Loja',
        ])->assertSessionHasErrors('format');
    }
}
