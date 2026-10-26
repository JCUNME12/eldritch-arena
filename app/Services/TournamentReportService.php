<?php

namespace App\Services;

use App\Models\Tournament;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class TournamentReportService
{
    private function check(bool $valid, string $message): void
    {
        if (! $valid) {
            throw ValidationException::withMessages(['report' => $message]);
        }
    }

    public function change(Tournament $event, User $actor, array $data): void
    {
        DB::transaction(function () use ($event, $actor, $data) {
            $t = Tournament::lockForUpdate()->findOrFail($event->id);
            abort_unless($t->organizer_id === $actor->id, 403);
            $this->check(! $t->cancelled_at, 'Torneio cancelado não pode ser apurado.');
            $this->check($t->results_revision === (int) $data['revision'], 'Esta página ficou desatualizada. Recarregue antes de salvar.');
            $action = $data['action'];
            $state = $t->results_state;
            $this->check(! $t->report_published_at || $action === 'reopen', 'Retire o report de publicação antes de corrigir.');
            if ($action === 'start') {
                $this->check(! $state && $t->starts_at->isPast(), 'A apuração começa após o horário do evento e apenas uma vez.');
                $ids = array_map('intval', $data['attendees']);
                $registered = $t->registrations()->where('status', 'confirmed')->pluck('id')->all();
                $this->check(count(array_diff($ids, $registered)) === 0, 'Selecione somente inscritos confirmados deste evento.');
                $players = [];
                foreach ($ids as $i => $id) {
                    $players[(string) $id] = ['alias' => 'Jogador '.($i + 1), 'archetype' => ''];
                }
                $state = ['players' => $players, 'matches' => [], 'city' => trim($data['city'] ?? ''), 'state' => strtoupper($data['state'] ?? '')];
                $t->results_started_at = now();
            } else {
                $this->check((bool) $state, 'Confirme os participantes antes de apurar.');
                if ($action === 'player') {
                    $id = (string) $data['player'];
                    $this->check(isset($state['players'][$id]), 'Participante inválido.');
                    $alias = trim(preg_replace('/\s+/u', ' ', $data['alias']));
                    $this->check($alias !== '', 'Informe um apelido público.');
                    foreach ($state['players'] as $key => $p) {
                        $this->check((string) $key === $id || mb_strtolower($p['alias']) !== mb_strtolower($alias), 'Use apelidos distintos.');
                    }
                    $state['players'][$id] = ['alias' => $alias, 'archetype' => trim(preg_replace('/\s+/u', ' ', $data['archetype'] ?? ''))];
                } elseif ($action === 'pair') {
                    $a = (int) $data['a'];
                    $b = isset($data['b']) ? (int) $data['b'] : null;
                    $round = (int) $data['round'];
                    $this->check(isset($state['players'][$a]) && (! $b || isset($state['players'][$b])) && $a !== $b, 'Escolha jogadores diferentes deste evento.');
                    $matches = array_filter($state['matches'], fn ($m) => ! $m['void']);
                    $max = $matches ? max(array_column($matches, 'round')) : 0;
                    $this->check($round <= max(1, $max + 1), 'Crie as rodadas em sequência.');
                    if ($round > $max && $max > 0) {
                        $this->complete($state);
                    }
                    foreach ($matches as $m) {
                        if ($m['round'] === $round) {
                            $this->check(! in_array($a, [$m['a'], $m['b']], true) && (! $b || ! in_array($b, [$m['a'], $m['b']], true)), 'Jogador já pareado nesta rodada.');
                            $this->check($b || $m['b'] !== null, 'A rodada já possui bye.');
                        }
                    }
                    $this->check($b || count($state['players']) % 2 === 1, 'Bye só é permitido com número ímpar de participantes.');
                    $state['matches'][] = ['a' => $a, 'b' => $b, 'round' => $round, 'outcome' => $b ? 'pending' : 'bye', 'void' => false];
                } elseif (in_array($action, ['result', 'void'], true)) {
                    $id = (int) $data['match'];
                    $this->check(isset($state['matches'][$id]) && ! $state['matches'][$id]['void'], 'Partida inexistente ou anulada.');
                    if ($action === 'void') {
                        $state['matches'][$id]['void'] = true;
                    } else {
                        $this->check($state['matches'][$id]['b'] !== null, 'Bye não possui resultado de duelo.');
                        $state['matches'][$id]['outcome'] = $data['outcome'];
                    }
                } elseif ($action === 'publish') {
                    $this->complete($state);
                    $t->report_snapshot = $this->snapshot($t, $state);
                    $t->report_published_at = now();
                } elseif ($action === 'reopen') {
                    $this->check((bool) $t->report_published_at, 'Este report não está publicado.');
                    $t->report_published_at = null;
                    $t->report_snapshot = null;
                }
            }
            $t->results_state = $state;
            $t->results_revision++;
            $t->save();
            DB::table('tournament_report_audits')->insert(['tournament_id' => $t->id, 'actor_id' => $actor->id, 'action' => $action, 'reason' => $data['reason'] ?? null, 'details' => json_encode(['revision' => $t->results_revision, 'input' => $data], JSON_UNESCAPED_UNICODE), 'created_at' => now()]);
        });
    }

    private function complete(array $state): void
    {
        $matches = array_filter($state['matches'], fn ($m) => ! $m['void']);
        $this->check(count($matches) > 0, 'Cadastre ao menos uma rodada completa.');
        for ($r = 1; $r <= max(array_column($matches, 'round')); $r++) {
            $seats = [];
            foreach ($matches as $m) {
                if ($m['round'] === $r) {
                    $this->check($m['outcome'] !== 'pending', "A rodada $r possui resultado pendente.");
                    $seats[] = $m['a'];
                    if ($m['b']) {
                        $seats[] = $m['b'];
                    }
                }
            }
            $this->check(count($seats) === count($state['players']) && count(array_unique($seats)) === count($seats), "Pareie todos os participantes na rodada $r.");
        }
    }

    public function snapshot(Tournament $t, array $state): array
    {
        $rows = [];
        $meta = [];
        $games = [];
        foreach ($state['players'] as $id => $p) {
            $rows[$id] = $p + ['wins' => 0, 'draws' => 0, 'losses' => 0, 'byes' => 0, 'points' => 0];
            $key = mb_strtolower($p['archetype'] ?: 'Não informado');
            if (! isset($meta[$key])) {
                $meta[$key] = ['name' => $p['archetype'] ?: 'Não informado', 'count' => 0];
            }
            $meta[$key]['count']++;
        }
        foreach ($state['matches'] as $m) {
            if ($m['void'] || $m['outcome'] === 'pending') {
                continue;
            }
            $a = $m['a'];
            $b = $m['b'];
            $o = $m['outcome'];
            if ($o === 'bye') {
                $rows[$a]['byes']++;
                $rows[$a]['points'] += 3;
            } elseif ($o === 'draw') {
                foreach ([$a, $b] as $id) {
                    $rows[$id]['draws']++;
                    $rows[$id]['points']++;
                }
            } else {
                $winner = $o === 'a_win' ? $a : $b;
                $loser = $o === 'a_win' ? $b : $a;
                $rows[$winner]['wins']++;
                $rows[$winner]['points'] += 3;
                $rows[$loser]['losses']++;
            }
            $games[] = ['bye' => $b === null, 'round' => $m['round'], 'a' => $rows[$a]['alias'], 'b' => $b ? $rows[$b]['alias'] : 'Bye', 'result' => match ($o) {
                'a_win' => 'Vitória de '.$rows[$a]['alias'],'b_win' => 'Vitória de '.$rows[$b]['alias'],'draw' => 'Empate',default => 'Bye (+3)'
            }];
        }
        usort($rows, fn ($a, $b) => ($b['points'] <=> $a['points']) ?: strcmp($a['alias'], $b['alias']));
        $rank = 0;
        $previous = null;
        foreach ($rows as $i => &$row) {
            if ($previous !== $row['points']) {
                $rank = $i + 1;
            }
            $previous = $row['points'];
            $row['rank'] = $rank;
            $played = $row['wins'] + $row['draws'] + $row['losses'];
            $row['win_rate'] = $played ? round(100 * $row['wins'] / $played, 1) : null;
        }
        unset($row);
        usort($meta, fn ($a, $b) => $b['count'] <=> $a['count']);

        return ['title' => $t->title, 'game' => $t->game, 'format' => $t->format, 'date' => $t->starts_at->timezone('America/Sao_Paulo')->format('d/m/Y'), 'city' => $state['city'], 'state' => $state['state'], 'players' => count($rows), 'standings' => $rows, 'meta' => array_values($meta), 'matches' => $games];
    }
}
