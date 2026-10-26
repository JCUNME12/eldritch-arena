<?php

namespace Tests\Feature;

use App\Models\PlayerDeck;
use App\Models\PlayerMatch;
use App\Models\User;
use App\Services\PlayerInsights;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class PlayerDeckTest extends TestCase
{
    use RefreshDatabase;

    private function owner(bool $premium = true): User
    {
        $user = User::factory()->create(['premium_active' => $premium, 'premium_plan' => $premium ? 'player_premium' : 'free']);
        $this->actingAs($user);

        return $user;
    }

    private function deck(): PlayerDeck
    {
        $this->post('/meus-decks', ['name' => 'Burn v1', 'game' => 'Magic: The Gathering', 'format' => 'Modern', 'archetype' => 'Burn'])->assertSessionHasNoErrors();

        return PlayerDeck::latest('id')->firstOrFail();
    }

    private function record(PlayerDeck $deck, array $overrides = []): PlayerMatch
    {
        $data = array_merge(['request_id' => (string) Str::uuid(), 'opponent' => 'Tron', 'result' => 'win', 'played_on' => now('America/Rio_Branco')->toDateString(), 'players' => 2, 'context' => 'casual'], $overrides);
        $this->post(route('decks.match.save', $deck), $data)->assertSessionHasNoErrors()->assertRedirect();

        return PlayerMatch::where('request_id', $data['request_id'])->firstOrFail();
    }

    public function test_free_players_keep_decks_and_history_but_cannot_access_real_insights_or_csv(): void
    {
        $this->owner(false);
        $deck = $this->deck();
        $match = $this->record($deck);
        foreach (['/meus-decks', '/meus-decks/novo', '/meus-decks/'.$deck->id, '/meus-decks/'.$deck->id.'/editar', '/meus-decks/'.$deck->id.'/partidas/nova', '/meus-decks/partidas/'.$match->id.'/editar', '/premium'] as $url) {
            $this->get($url)->assertOk();
        }
        $this->get('/meus-decks/analises')->assertForbidden();
        $this->get('/meus-decks/exportar')->assertForbidden();
        $this->get('/meus-decks/exemplo')->assertOk()->assertSee('dados fictícios');
        $this->assertDatabaseCount('player_matches', 1);
    }

    public function test_rates_include_draws_normalize_opponents_and_exclude_other_tables_and_users(): void
    {
        $user = $this->owner();
        $deck = $this->deck();
        $this->record($deck);
        $this->record($deck, ['opponent' => '  TRON  ', 'result' => 'draw']);
        $this->record($deck, ['result' => 'loss']);
        $this->record($deck, ['players' => 4, 'opponent' => 'Mesa mista']);
        $this->owner();
        $other = $this->deck();
        $this->record($other, ['opponent' => 'PRIVADO']);
        $this->actingAs($user)->get('/meus-decks/analises')->assertOk()->assertSee('33,3%')->assertSee('Amostra pequena')->assertDontSee('Mesa mista')->assertDontSee('PRIVADO');
        $this->get('/meus-decks/analises?format=Commander')->assertOk()->assertSee('Ainda não há partidas');
        $this->get('/meus-decks/analises?players=4')->assertOk()->assertSee('100,0%')->assertSee('Mesa mista');
        $this->get('/meus-decks/analises?deck='.$other->id)->assertNotFound();
    }

    public function test_corrections_removal_restore_and_archive_preserve_history(): void
    {
        $this->owner();
        $deck = $this->deck();
        $match = $this->record($deck);
        $this->put(route('decks.match.update', $match), ['opponent' => 'Tron', 'result' => 'loss', 'played_on' => now('America/Rio_Branco')->toDateString(), 'players' => 2, 'context' => 'casual'])->assertSessionHasNoErrors();
        $this->get('/meus-decks/analises')->assertSee('0,0%');
        $this->delete(route('decks.match.remove', $match))->assertRedirect();
        $this->get('/meus-decks/analises')->assertSee('Ainda não há partidas');
        $this->get(route('decks.show', [$deck, 'removed' => 1]))->assertSee('Restaurar registro');
        $this->post(route('decks.match.restore', $match))->assertRedirect();
        $this->assertDatabaseCount('player_matches', 1);
        $this->patch(route('decks.archive', $deck), ['archived' => 1])->assertRedirect();
        $this->get(route('decks.match.create', $deck))->assertForbidden();
        $this->get('/meus-decks/analises')->assertSee('Burn v1');
        $this->patch(route('decks.archive', $deck), ['archived' => 0])->assertRedirect();
        $this->get(route('decks.match.create', $deck))->assertOk();
    }

    public function test_cross_owner_mutations_exports_and_restore_are_forbidden(): void
    {
        $this->owner();
        $deck = $this->deck();
        $match = $this->record($deck);
        $match->delete();
        $this->owner();
        $this->get(route('decks.show', $deck))->assertNotFound();
        $this->put(route('decks.update', $deck), [])->assertNotFound();
        $this->post(route('decks.match.save', $deck), [])->assertNotFound();
        $this->post(route('decks.match.restore', $match))->assertNotFound();
        $this->get('/meus-decks/exportar?deck='.$deck->id)->assertNotFound();
        $this->assertTrue($match->fresh()->trashed());
    }

    public function test_validation_and_idempotency_and_locked_game_identity(): void
    {
        $this->owner();
        $deck = $this->deck();
        $id = (string) Str::uuid();
        $this->record($deck, ['request_id' => $id]);
        $this->record($deck, ['request_id' => $id]);
        $this->assertDatabaseCount('player_matches', 1);
        $this->post(route('decks.match.save', $deck), ['request_id' => $id, 'opponent' => 'Tron', 'result' => 'loss', 'played_on' => now('America/Rio_Branco')->toDateString(), 'players' => 2, 'context' => 'casual'])->assertSessionHasErrors('request_id');
        $this->post('/meus-decks', ['name' => 'Invalid', 'game' => 'Yu-Gi-Oh!', 'format' => 'Commander'])->assertSessionHasErrors('format');
        $this->put(route('decks.update', $deck), ['name' => 'Burn', 'game' => 'Magic: The Gathering', 'format' => 'Commander'])->assertSessionHasErrors('format');
        $this->post(route('decks.match.save', $deck), ['request_id' => (string) Str::uuid(), 'opponent' => 'Tron', 'result' => 'win', 'played_on' => now('America/Rio_Branco')->addDay()->toDateString(), 'players' => 2, 'context' => 'casual'])->assertSessionHasErrors('played_on');
        $this->assertDatabaseCount('player_matches', 1);
    }

    public function test_premium_cancellation_keeps_data_and_filters_apply_to_csv(): void
    {
        $user = $this->owner();
        $deck = $this->deck();
        $this->record($deck, ['opponent' => '=1+1']);
        $this->record($deck, ['opponent' => 'OLD', 'played_on' => now('America/Rio_Branco')->subDays(100)->toDateString()]);
        $this->record($deck, ['opponent' => 'TOURNAMENT', 'context' => 'tournament']);
        $csv = $this->get('/meus-decks/exportar?period=30&context=casual')->assertOk()->streamedContent();
        $this->assertStringContainsString("'=1+1", $csv);
        $this->assertStringNotContainsString('OLD', $csv);
        $this->assertStringNotContainsString('TOURNAMENT', $csv);
        $this->delete('/premium')->assertRedirect();
        $this->actingAs($user->fresh())->get('/meus-decks/analises')->assertForbidden();
        $this->get(route('decks.show', $deck))->assertOk()->assertSee('OLD');
        $this->assertDatabaseCount('player_matches', 3);
    }

    public function test_summary_empty_data_months_and_current_streak_are_exact(): void
    {
        $service = new PlayerInsights;
        $this->assertNull($service->calculate(collect())['summary']['rate']);
        $rows = collect();
        foreach (['loss', 'draw', 'win', 'win'] as $i => $result) {
            $rows->push(['id' => $i + 1, 'deck_id' => 1, 'deck' => 'Test', 'game' => 'Magic', 'format' => 'Modern', 'opponent' => 'Tron', 'opponent_key' => 'tron', 'result' => $result, 'date' => '2026-10-01']);
        }
        $data = $service->calculate($rows);
        $this->assertSame(50.0, $data['summary']['rate']);
        $this->assertSame(2, $data['streak']);
        $this->assertCount(1,$data['months']);
        $this->assertCount(1,$data['matchups']);
        $this->assertSame(4,$data['recent']['total']);
    }
}
