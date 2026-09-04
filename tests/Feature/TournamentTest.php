<?php

namespace Tests\Feature;

use App\Models\Tournament;
use App\Models\TournamentRegistration;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TournamentTest extends TestCase
{
    use RefreshDatabase;

    public function test_only_organizers_can_open_the_tournament_creation_page(): void
    {
        $player = User::factory()->create(['type' => 'player']);
        $organizer = User::factory()->create(['type' => 'organizer']);

        $this->actingAs($player)->get(route('tournaments.create'))->assertForbidden();
        $this->actingAs($organizer)->get(route('tournaments.create'))->assertOk();
    }

    public function test_organizer_can_create_a_tournament(): void
    {
        $organizer = User::factory()->create([
            'type' => 'organizer',
            'premium_active' => false,
        ]);

        $response = $this->actingAs($organizer)->post(route('tournaments.store'), [
            'title' => 'Open Eldritch',
            'game' => 'Magic: The Gathering',
            'starts_at' => now()->addWeek()->format('Y-m-d H:i:s'),
            'prize' => 'Troféu e boosters',
            'entry_fee' => 30,
            'slots' => 16,
            'location' => 'Arena Central',
            'description' => 'Primeiro torneio oficial da temporada.',
        ]);

        $tournament = Tournament::query()->sole();

        $response->assertRedirect(route('tournaments.show', $tournament));
        $this->assertDatabaseHas('tournaments', [
            'organizer_id' => $organizer->id,
            'title' => 'Open Eldritch',
            'status' => Tournament::STATUS_PUBLISHED,
            'highlighted' => false,
        ]);
    }

    public function test_past_tournaments_cannot_be_created(): void
    {
        $organizer = User::factory()->create(['type' => 'organizer']);

        $this->actingAs($organizer)->post(route('tournaments.store'), [
            'title' => 'Torneio antigo',
            'game' => 'Pokémon',
            'starts_at' => now()->subDay()->format('Y-m-d H:i:s'),
            'prize' => 'Troféu',
            'entry_fee' => 0,
            'slots' => 8,
            'location' => 'Arena Central',
        ])->assertSessionHasErrors('starts_at');
    }

    public function test_registration_is_idempotent(): void
    {
        $player = User::factory()->create(['type' => 'player']);
        $tournament = Tournament::factory()->create();

        $this->actingAs($player)->post(route('tournaments.register', $tournament))->assertSessionHas('status');
        $this->actingAs($player)->post(route('tournaments.register', $tournament))->assertSessionHas('status');

        $this->assertDatabaseCount('tournament_registrations', 1);
    }

    public function test_registration_is_rejected_when_tournament_is_full(): void
    {
        $tournament = Tournament::factory()->create(['slots' => 2]);
        $registeredPlayers = User::factory()->count(2)->create(['type' => 'player']);

        foreach ($registeredPlayers as $player) {
            TournamentRegistration::create([
                'tournament_id' => $tournament->id,
                'user_id' => $player->id,
                'status' => 'confirmed',
            ]);
        }

        $waitingPlayer = User::factory()->create(['type' => 'player']);

        $this->actingAs($waitingPlayer)
            ->post(route('tournaments.register', $tournament))
            ->assertSessionHasErrors('tournament');

        $this->assertDatabaseMissing('tournament_registrations', [
            'tournament_id' => $tournament->id,
            'user_id' => $waitingPlayer->id,
        ]);
    }

    public function test_registration_is_rejected_for_cancelled_tournament(): void
    {
        $player = User::factory()->create(['type' => 'player']);
        $tournament = Tournament::factory()->create(['status' => Tournament::STATUS_CANCELLED]);

        $this->actingAs($player)
            ->post(route('tournaments.register', $tournament))
            ->assertSessionHasErrors('tournament');
    }

    public function test_organizer_can_update_and_cancel_their_tournament(): void
    {
        $organizer = User::factory()->create(['type' => 'organizer']);
        $tournament = Tournament::factory()->create(['organizer_id' => $organizer->id]);

        $this->actingAs($organizer)->put(route('tournaments.update', $tournament), [
            'title' => 'Open Eldritch Atualizado',
            'game' => 'Pokémon',
            'starts_at' => now()->addDays(10)->format('Y-m-d H:i:s'),
            'prize' => 'Troféu e boosters',
            'entry_fee' => 45,
            'slots' => 24,
            'location' => 'Arena Norte',
            'description' => 'Evento oficial atualizado.',
        ])->assertRedirect(route('tournaments.show', $tournament));

        $this->assertDatabaseHas('tournaments', ['id' => $tournament->id, 'title' => 'Open Eldritch Atualizado']);

        $this->actingAs($organizer)->patch(route('tournaments.cancel', $tournament))->assertRedirect();
        $this->assertSame(Tournament::STATUS_CANCELLED, $tournament->fresh()->status);
    }

    public function test_player_can_cancel_their_registration(): void
    {
        $player = User::factory()->create(['type' => 'player']);
        $tournament = Tournament::factory()->create();
        TournamentRegistration::create([
            'tournament_id' => $tournament->id,
            'user_id' => $player->id,
            'status' => 'confirmed',
        ]);

        $this->actingAs($player)->delete(route('tournaments.unregister', $tournament))->assertRedirect();
        $this->assertDatabaseMissing('tournament_registrations', [
            'tournament_id' => $tournament->id,
            'user_id' => $player->id,
        ]);
    }
}
