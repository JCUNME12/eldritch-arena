<?php

namespace App\Http\Controllers;

use App\Models\Tournament;
use App\Services\TournamentReportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class TournamentReportController extends Controller
{
    public function index(Request $r)
    {
        $filters = $r->validate(['game' => ['nullable', Rule::in(array_keys(config('cardgames')))], 'format' => ['nullable', 'string', 'max:80'], 'city' => ['nullable', 'string', 'max:80'], 'state' => ['nullable', 'regex:/^[A-Z]{2}$/']]);
        $events = Tournament::whereNotNull('report_published_at')->whereNull('cancelled_at')
            ->when($filters['game'] ?? null, fn ($q, $v) => $q->where('game', $v))->when($filters['format'] ?? null, fn ($q, $v) => $q->where('format', $v))
            ->when($filters['city'] ?? null, fn ($q, $v) => $q->where('report_snapshot->city', trim($v)))
            ->when($filters['state'] ?? null, fn ($q, $v) => $q->where('report_snapshot->state', $v))
            ->latest('starts_at')->paginate(12)->withQueryString();

        return view('reports.index', compact('events'));
    }

    public function show(Tournament $tournament)
    {
        abort_unless($tournament->report_published_at && ! $tournament->cancelled_at, 404);

        return view('reports.show', ['report' => $tournament->report_snapshot, 'example' => false]);
    }

    public function manage(Request $r, Tournament $tournament, TournamentReportService $service)
    {
        abort_unless($r->user()->id === $tournament->organizer_id, 403);
        $tournament->load('registrations.user');
        $preview = $tournament->results_state ? $service->snapshot($tournament, $tournament->results_state) : null;
        $audits = DB::table('tournament_report_audits')->where('tournament_id', $tournament->id)->latest('id')->limit(20)->get();

        return view('reports.manage', compact('tournament', 'preview', 'audits'));
    }

    public function change(Request $r, Tournament $tournament, TournamentReportService $service)
    {
        abort_unless($r->user()->id === $tournament->organizer_id, 403);
        $base = $r->validate(['action' => ['required', Rule::in(['start', 'player', 'pair', 'result', 'void', 'publish', 'reopen'])], 'revision' => ['required', 'integer', 'min:0']]);
        $rules = match ($base['action']) {
            'start' => ['attendees' => ['required', 'array', 'min:2', 'max:256'], 'attendees.*' => ['required', 'integer', 'distinct'], 'duels' => ['accepted'], 'city' => ['nullable', 'string', 'max:80'], 'state' => ['nullable', 'regex:/^[a-zA-Z]{2}$/']],
            'player' => ['player' => ['required', 'integer'], 'alias' => ['required', 'string', 'max:50'], 'archetype' => ['nullable', 'string', 'max:100']],
            'pair' => ['round' => ['required', 'integer', 'min:1', 'max:15'], 'a' => ['required', 'integer', 'min:1'], 'b' => ['nullable', 'integer', 'min:1']],
            'result' => ['match' => ['required', 'integer', 'min:0'], 'outcome' => ['required', Rule::in(['a_win', 'b_win', 'draw'])], 'reason' => ['required', 'string', 'min:3', 'max:500']],
            'void' => ['match' => ['required', 'integer', 'min:0'], 'reason' => ['required', 'string', 'min:3', 'max:500']],
            'reopen' => ['reason' => ['required', 'string', 'min:3', 'max:500']],
            default => ['confirmed' => ['accepted']],
        };
        $service->change($tournament, $r->user(), $base + $r->validate($rules));

        return back()->with('status', 'Apuração atualizada.');
    }

    public function example(TournamentReportService $service)
    {
        $t = new Tournament(['title' => 'Circuito Arena · exemplo fictício', 'game' => 'Magic: The Gathering', 'format' => 'Modern', 'starts_at' => '2026-10-26']);
        $state = ['city' => 'Rio Branco', 'state' => 'AC', 'players' => [1 => ['alias' => 'Jogador 1', 'archetype' => 'Burn'], 2 => ['alias' => 'Jogador 2', 'archetype' => 'Azorius Control'], 3 => ['alias' => 'Jogador 3', 'archetype' => 'Burn'], 4 => ['alias' => 'Jogador 4', 'archetype' => 'Tron']], 'matches' => [
            ['a' => 1, 'b' => 2, 'round' => 1, 'outcome' => 'a_win', 'void' => false], ['a' => 3, 'b' => 4, 'round' => 1, 'outcome' => 'draw', 'void' => false],
            ['a' => 1, 'b' => 3, 'round' => 2, 'outcome' => 'b_win', 'void' => false], ['a' => 2, 'b' => 4, 'round' => 2, 'outcome' => 'a_win', 'void' => false]]];

        return view('reports.show', ['report' => $service->snapshot($t, $state), 'example' => true]);
    }
}
