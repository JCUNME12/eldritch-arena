<?php

namespace Tests\Feature;

use App\Models\Tournament;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TournamentReportsTest extends TestCase
{
    use RefreshDatabase;

    private function event(int $players = 4): Tournament
    {
        $owner = User::factory()->create();
        $t = Tournament::create(['organizer_id' => $owner->id, 'title' => 'Copa teste', 'game' => 'Magic: The Gathering', 'format' => 'Modern', 'starts_at' => now()->subHour(), 'slots' => 16, 'prize' => 'Cartas', 'entry_fee' => 0, 'location' => 'Endereço privado']);
        foreach (User::factory()->count($players)->create() as $u) {
            $t->registrations()->create(['user_id' => $u->id, 'status' => 'confirmed']);
        }
        $this->actingAs($owner);

        return $t;
    }

    private function action(Tournament $t, string $action, array $extra = [])
    {
        return $this->post(route('reports.change', $t), array_merge(['action' => $action, 'revision' => $t->fresh()->results_revision], $extra));
    }

    private function start(Tournament $t): array
    {
        $ids = $t->registrations()->pluck('id')->all();
        $this->action($t, 'start', ['attendees' => $ids, 'duels' => 1, 'city' => 'Rio Branco', 'state' => 'ac'])->assertSessionHasNoErrors();

        return $ids;
    }

    public function test_publication_has_exact_standings_meta_and_no_private_data(): void
    {
        $t = $this->event();
        $ids = $this->start($t);
        foreach ($ids as $i => $id) {
            $this->action($t, 'player', ['player' => $id, 'alias' => 'Mesa '.($i + 1), 'archetype' => $i < 2 ? ' Burn ' : 'Tron'])->assertSessionHasNoErrors();
        }
        foreach ([[0, 1], [2, 3]] as [$a,$b]) {
            $this->action($t, 'pair', ['round' => 1, 'a' => $ids[$a], 'b' => $ids[$b]])->assertSessionHasNoErrors();
        }
        $this->action($t, 'result', ['match' => 0, 'outcome' => 'a_win', 'reason' => 'Mesa conferida'])->assertSessionHasNoErrors();
        $this->action($t, 'result', ['match' => 1, 'outcome' => 'draw', 'reason' => 'Mesa conferida'])->assertSessionHasNoErrors();
        $this->get(route('reports.manage', $t))->assertOk()->assertSee('Prévia');
        $this->get(route('reports.show', $t))->assertNotFound();
        $this->action($t, 'publish', ['confirmed' => 1])->assertSessionHasNoErrors();
        $s = $t->fresh()->report_snapshot;
        $this->assertSame([3, 1, 1, 0], array_column($s['standings'], 'points'));
        $this->assertSame([1, 2, 2, 4], array_column($s['standings'], 'rank'));
        $this->assertEquals(100, $s['standings'][0]['win_rate']);
        $this->assertSame([2, 2], array_column($s['meta'], 'count'));
        $this->app['auth']->forgetGuards();
        $page = $this->get(route('reports.show', $t))->assertOk()->assertSee('50,0%')->assertDontSee('Endereço privado');
        foreach ($t->registrations as $r) {
            $page->assertDontSee($r->user->email);
            $page->assertDontSee($r->user->name);
        }
        $this->get('/reports?game=Pokémon')->assertOk()->assertDontSee('Copa teste');
        $this->get('/reports')->assertOk()->assertSee('Copa teste');
        $this->get('/reports?city=Rio%20Branco&state=AC')->assertOk()->assertSee('Copa teste');
        $this->get('/reports?state=SP')->assertOk()->assertDontSee('Copa teste');
    }

    public function test_pending_missing_and_double_seating_cannot_publish_or_start_next_round(): void
    {
        $t = $this->event();
        $ids = $this->start($t);
        $this->action($t, 'pair', ['round' => 1, 'a' => $ids[0], 'b' => $ids[1]])->assertSessionHasNoErrors();
        $this->action($t, 'pair', ['round' => 1, 'a' => $ids[0], 'b' => $ids[2]])->assertSessionHasErrors('report');
        $this->action($t, 'pair', ['round' => 1, 'a' => $ids[2]])->assertSessionHasErrors('report');
        $this->action($t, 'publish', ['confirmed' => 1])->assertSessionHasErrors('report');
        $this->action($t, 'result', ['match' => 0, 'outcome' => 'draw', 'reason' => 'Conferido'])->assertSessionHasNoErrors();
        $this->action($t, 'publish', ['confirmed' => 1])->assertSessionHasErrors('report');
        $this->action($t, 'pair', ['round' => 2, 'a' => $ids[0], 'b' => $ids[1]])->assertSessionHasErrors('report');
    }

    public function test_bye_is_points_without_a_win_and_corrections_withdraw_publication(): void
    {
        $t = $this->event(3);
        $ids = $this->start($t);
        $this->action($t, 'pair', ['round' => 1, 'a' => $ids[0]])->assertSessionHasNoErrors();
        $this->action($t, 'pair', ['round' => 1, 'a' => $ids[1]])->assertSessionHasErrors('report');
        $this->action($t, 'pair', ['round' => 1, 'a' => $ids[1], 'b' => $ids[2]])->assertSessionHasNoErrors();
        $this->action($t, 'result', ['match' => 1, 'outcome' => 'draw', 'reason' => 'Confirmado'])->assertSessionHasNoErrors();
        $this->action($t, 'publish', ['confirmed' => 1])->assertSessionHasNoErrors();
        $row = $t->fresh()->report_snapshot['standings'][0];
        $this->assertSame(3, $row['points']);
        $this->assertSame(0, $row['wins']);
        $this->assertNull($row['win_rate']);
        $this->action($t, 'result', ['match' => 1, 'outcome' => 'a_win', 'reason' => 'Correção'])->assertSessionHasErrors('report');
        $this->action($t, 'reopen', ['reason' => 'Correção da mesa 2'])->assertSessionHasNoErrors();
        $this->get(route('reports.show', $t))->assertNotFound();
        $this->action($t, 'void', ['match' => 1, 'reason' => 'Pareamento incorreto'])->assertSessionHasNoErrors();
        $this->action($t, 'publish', ['confirmed' => 1])->assertSessionHasErrors('report');
        $this->action($t, 'pair', ['round' => 1, 'a' => $ids[1], 'b' => $ids[2]])->assertSessionHasNoErrors();
        $this->action($t, 'result', ['match' => 2, 'outcome' => 'a_win', 'reason' => 'Corrigido'])->assertSessionHasNoErrors();
        $this->action($t, 'publish', ['confirmed' => 1])->assertSessionHasNoErrors();
        $this->assertDatabaseHas('tournament_report_audits', ['tournament_id' => $t->id, 'action' => 'reopen', 'reason' => 'Correção da mesa 2']);
    }

    public function test_owner_validation_revision_and_registration_freeze(): void
    {
        $t = $this->event(2);
        $ids = $this->start($t);
        $this->action($t, 'player', ['player' => $ids[0], 'alias' => 'Mesa', 'revision' => 0])->assertSessionHasErrors('report');
        $this->action($t, 'pair', ['round' => 1, 'a' => $ids[0], 'b' => 999999])->assertSessionHasErrors('report');
        $this->patch(route('tournaments.cancel', $t))->assertSessionHasErrors('tournament');
        $this->actingAs($t->registrations->first()->user);
        $this->delete(route('tournaments.unregister', $t))->assertSessionHasErrors('tournament');
        $this->get(route('reports.manage', $t))->assertForbidden();
        $this->action($t, 'publish', ['confirmed' => 1])->assertForbidden();
    }

    public function test_attendance_cannot_be_forged_and_future_events_cannot_start(): void
    {
        $t = $this->event(2);
        $ids = $t->registrations()->pluck('id')->all();
        $this->action($t, 'start', ['attendees' => [$ids[0], 99999], 'duels' => 1])->assertSessionHasErrors('report');
        $this->action($t, 'start', ['attendees' => $ids])->assertSessionHasErrors('duels');
        $t->update(['starts_at' => now()->addDay()]);
        $this->action($t, 'start', ['attendees' => $ids, 'duels' => 1])->assertSessionHasErrors('report');
        $this->assertNull($t->fresh()->results_started_at);
    }

    public function test_example_is_public_and_does_not_create_fake_events(): void
    {
        $this->get('/reports/exemplo')->assertOk()->assertSee('Demonstração fictícia')->assertSee('50,0%');
        $this->assertDatabaseCount('tournaments', 0);
    }
}
