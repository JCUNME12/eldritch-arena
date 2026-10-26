<?php

namespace App\Services;

use Illuminate\Support\Collection;

class PlayerInsights
{
    public function summary(Collection $rows): array
    {
        $total = $rows->count();
        $wins = $rows->where('result', 'win')->count();

        return ['total' => $total, 'wins' => $wins, 'losses' => $rows->where('result', 'loss')->count(), 'draws' => $rows->where('result', 'draw')->count(), 'rate' => $total ? round($wins * 100 / $total, 1) : null];
    }

    public function calculate(Collection $rows): array
    {
        $summary = $this->summary($rows);
        $decks = $rows->groupBy('deck_id')->map(function ($group) {
            return $this->summary($group) + ['label' => $group->first()['deck'], 'game' => $group->first()['game'], 'format' => $group->first()['format']];
        })->sortByDesc('total')->values();
        // Keep each deck's matchups separate, including its game and format identity.
        $matchups = $rows->groupBy(fn ($r) => json_encode([$r['deck_id'], $r['opponent_key']]))->map(function ($group) {
            return $this->summary($group) + ['deck' => $group->first()['deck'], 'opponent' => $group->first()['opponent'], 'game' => $group->first()['game'], 'format' => $group->first()['format']];
        })->sortByDesc('total')->values();
        $months = $rows->groupBy(fn ($r) => substr($r['date'], 0, 7))->sortKeys()->map(fn ($group, $month) => $this->summary($group) + ['month' => $month])->values();
        $ordered = $rows->sortByDesc(fn ($r) => $r['date'].'-'.str_pad((string) $r['id'], 20, '0', STR_PAD_LEFT))->values();
        $streak = $ordered->takeWhile(fn ($r) => $r['result'] === 'win')->count();

        return compact('summary', 'decks', 'matchups', 'months', 'streak') + ['recent' => $this->summary($ordered->take(10))];
    }
}
