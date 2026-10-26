<?php

namespace App\Http\Controllers;

use App\Models\PlayerDeck;
use App\Models\PlayerMatch;
use App\Services\PlayerInsights;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class PlayerDeckController extends Controller
{
    private function owned(Request $r, PlayerDeck $deck): void
    {
        abort_unless($deck->user_id === $r->user()->id, 404);
    }

    private function premium(Request $r): void
    {
        abort_unless($r->user()->isPremium(), 403, 'Análises e exportação disponíveis no Arena Plus. Seu histórico continua acessível.');
    }

    public function index(Request $r)
    {
        $archived = $r->boolean('archived');
        $decks = PlayerDeck::where('user_id', $r->user()->id)->where('archived', $archived)->withCount('matches')->latest('id')->paginate(12);

        return view('player.index', compact('decks', 'archived'));
    }

    public function create()
    {
        return view('player.deck-form', ['deck' => new PlayerDeck]);
    }

    public function edit(Request $r, PlayerDeck $deck)
    {
        $this->owned($r, $deck);

        return view('player.deck-form', compact('deck'));
    }

    private function deckData(Request $r): array
    {
        $games = config('cardgames');
        $game = $r->input('game');

        return $r->validate(['name' => ['required', 'string', 'max:100'], 'game' => ['required', 'string', Rule::in(array_keys($games))], 'format' => ['required', 'string', Rule::in(is_string($game) ? ($games[$game] ?? []) : [])], 'archetype' => ['nullable', 'string', 'max:100'], 'notes' => ['nullable', 'string', 'max:2000']]);
    }

    public function save(Request $r)
    {
        $deck = new PlayerDeck($this->deckData($r));
        $deck->user_id = $r->user()->id;
        $deck->save();

        return redirect()->route('decks.show', $deck)->with('status', 'Deck cadastrado. Registre suas partidas para construir seu histórico.');
    }

    public function update(Request $r, PlayerDeck $deck)
    {
        $this->owned($r, $deck);
        $data = $this->deckData($r);
        DB::transaction(function () use ($deck, $data) {
            $locked = PlayerDeck::whereKey($deck->id)->lockForUpdate()->firstOrFail();
            if ($locked->matches()->withTrashed()->exists() && ($data['game'] !== $locked->game || $data['format'] !== $locked->format)) {
                throw ValidationException::withMessages(['format' => 'Este deck já possui histórico. Crie outro deck para mudar jogo ou formato.']);
            }
            $locked->update($data);
        });

        return redirect()->route('decks.show', $deck)->with('status', 'Deck atualizado.');
    }

    public function archive(Request $r, PlayerDeck $deck)
    {
        $this->owned($r, $deck);
        $r->validate(['archived' => ['required', 'boolean']]);
        DB::transaction(function () use ($r, $deck) {
            $locked = PlayerDeck::whereKey($deck->id)->lockForUpdate()->firstOrFail();
            $locked->archived = $r->boolean('archived');
            $locked->save();
        });

        return back()->with('status', 'Situação do deck atualizada; histórico preservado.');
    }

    public function show(Request $r, PlayerDeck $deck)
    {
        $this->owned($r, $deck);
        $removed = $r->boolean('removed');
        $matches = $deck->matches()->when($removed, fn ($q) => $q->onlyTrashed())->orderByDesc('played_on')->orderByDesc('id')->paginate(15)->withQueryString();

        return view('player.show', compact('deck', 'matches', 'removed'));
    }

    public function newMatch(Request $r, PlayerDeck $deck)
    {
        $this->owned($r, $deck);
        abort_if($deck->archived, 403, 'Reative o deck antes de registrar partidas.');

        return view('player.match-form', ['deck' => $deck, 'match' => new PlayerMatch, 'requestId' => (string) Str::uuid()]);
    }

    private function matchData(Request $r): array
    {
        $data = $r->validate(['opponent' => ['required', 'string', 'max:100'], 'result' => ['required', 'in:win,loss,draw'], 'played_on' => ['required', 'date_format:Y-m-d', 'after_or_equal:1993-01-01', 'before_or_equal:'.now('America/Rio_Branco')->toDateString()], 'players' => ['required', 'integer', 'min:2', 'max:6'], 'context' => ['required', 'in:casual,tournament'], 'notes' => ['nullable', 'string', 'max:2000']]);
        $data['opponent'] = Str::squish($data['opponent']);
        if ($data['opponent'] === '') {
            throw ValidationException::withMessages(['opponent' => 'Informe o deck adversário ou composição da mesa.']);
        }
        $data['opponent_key'] = Str::lower($data['opponent']);

        return $data;
    }

    public function saveMatch(Request $r, PlayerDeck $deck)
    {
        $this->owned($r, $deck);
        $data = $this->matchData($r);
        $data += $r->validate(['request_id' => ['required', 'uuid']]);
        DB::transaction(function () use ($deck, $data) {
            $locked = PlayerDeck::whereKey($deck->id)->lockForUpdate()->firstOrFail();
            abort_if($locked->archived, 403, 'Reative o deck antes de registrar partidas.');
            $existing = PlayerMatch::withTrashed()->where('request_id', $data['request_id'])->first();
            if ($existing) {
                $same = $existing->player_deck_id === $deck->id && ! $existing->trashed();
                foreach (['opponent', 'result', 'players', 'context', 'notes'] as $key) {
                    $same = $same && (string) $existing->$key === (string) ($data[$key] ?? '');
                }
                $same = $same && $existing->played_on->toDateString() === $data['played_on'];
                if (! $same) {
                    throw ValidationException::withMessages(['request_id' => 'Este envio já foi utilizado. Abra um novo registro.']);
                }

                return;
            }
            $locked->matches()->create($data);
        });

        return redirect()->route('decks.show', $deck)->with('status', 'Partida registrada.');
    }

    public function editMatch(Request $r, PlayerMatch $match)
    {
        $this->owned($r, $match->deck);

        return view('player.match-form', ['deck' => $match->deck, 'match' => $match, 'requestId' => $match->request_id]);
    }

    public function updateMatch(Request $r, PlayerMatch $match)
    {
        $this->owned($r, $match->deck);
        $match->update($this->matchData($r));

        return redirect()->route('decks.show', $match->deck)->with('status', 'Resultado corrigido; as análises refletem a alteração.');
    }

    public function removeMatch(Request $r, PlayerMatch $match)
    {
        $this->owned($r, $match->deck);
        $match->delete();

        return back()->with('status', 'Partida removida das análises. Você pode restaurá-la em Registros removidos.');
    }

    public function restoreMatch(Request $r, PlayerMatch $match)
    {
        $this->owned($r, $match->deck);
        $match->restore();

        return redirect()->route('decks.show', $match->deck)->with('status', 'Partida restaurada.');
    }

    private function filters(Request $r): array
    {
        return array_merge(['period' => '90', 'players' => 2, 'context' => '', 'deck' => null, 'game' => '', 'format' => ''], $r->validate(['period' => ['nullable', 'in:30,90,365,all'], 'players' => ['nullable', 'integer', 'min:2', 'max:6'], 'context' => ['nullable', 'in:casual,tournament'], 'deck' => ['nullable', 'integer'], 'game' => ['nullable', 'string', Rule::in(array_keys(config('cardgames')))], 'format' => ['nullable', 'string', Rule::in(collect(config('cardgames'))->flatten()->unique()->all())]]));
    }

    private function rows(Request $r, array $filters)
    {
        if ($filters['deck']) {
            PlayerDeck::where('user_id', $r->user()->id)->findOrFail($filters['deck']);
        }

        return PlayerMatch::with('deck')->whereHas('deck', fn ($q) => $q->where('user_id', $r->user()->id))
            ->when($filters['game'] ?? null, fn ($q, $v) => $q->whereHas('deck', fn ($d) => $d->where('game', $v)))
            ->when($filters['format'] ?? null, fn ($q, $v) => $q->whereHas('deck', fn ($d) => $d->where('format', $v)))
            ->when($filters['deck'], fn ($q, $id) => $q->where('player_deck_id', $id))
            ->where('players', $filters['players'] ?: 2)
            ->when($filters['context'], fn ($q, $value) => $q->where('context', $value))
            ->when(($filters['period'] ?: '90') !== 'all', fn ($q) => $q->whereDate('played_on', '>=', now('America/Rio_Branco')->subDays((int) ($filters['period'] ?: 90) - 1)->toDateString()))
            ->orderByDesc('played_on')->orderByDesc('id')->get()->map(fn ($m) => ['id' => $m->id, 'deck_id' => $m->player_deck_id, 'deck' => $m->deck->name, 'game' => $m->deck->game, 'format' => $m->deck->format, 'opponent' => $m->opponent, 'opponent_key' => $m->opponent_key, 'result' => $m->result, 'date' => $m->played_on->toDateString(), 'players' => $m->players, 'context' => $m->context]);
    }

    public function insights(Request $r, PlayerInsights $service)
    {
        $this->premium($r);
        $filters = $this->filters($r);
        $decks = PlayerDeck::where('user_id', $r->user()->id)->orderBy('name')->get();

        return view('player.insights', ['analysis' => $service->calculate($this->rows($r, $filters)), 'filters' => $filters, 'decks' => $decks, 'demo' => false]);
    }

    public function example(PlayerInsights $service)
    {
        $rows = collect();
        for ($i = 0; $i < 24; $i++) {
            $rows->push(['id' => $i + 1, 'deck_id' => $i % 2 + 1, 'deck' => $i % 2 ? 'Controle Azorius · exemplo' : 'Burn · exemplo', 'game' => 'Magic: The Gathering', 'format' => 'Modern', 'opponent' => $i % 3 ? 'Tron' : 'Murktide', 'opponent_key' => $i % 3 ? 'tron' : 'murktide', 'result' => ['win', 'loss', 'win', 'draw', 'win', 'loss', 'loss'][$i % 7], 'date' => now('America/Rio_Branco')->subDays($i * 3)->toDateString(), 'players' => 2, 'context' => 'casual']);
        }

        return view('player.insights', ['analysis' => $service->calculate($rows), 'filters' => ['period' => '90', 'players' => 2, 'context' => 'casual', 'deck' => null], 'decks' => collect(), 'demo' => true]);
    }

    public function export(Request $r)
    {
        $this->premium($r);
        $rows = $this->rows($r, $this->filters($r));

        return response()->streamDownload(function () use ($rows) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF");
            fputcsv($out, ['Data', 'Deck', 'Jogo', 'Formato', 'Adversário/mesa', 'Resultado', 'Jogadores', 'Contexto'], ';', '"', '');
            $safe = fn ($v) => preg_match('/^\s*[=+@\-]/u', (string) $v) ? "'".$v : $v;
            foreach ($rows as $row) {
                fputcsv($out, array_map($safe, [$row['date'], $row['deck'], $row['game'], $row['format'], $row['opponent'], ['win' => 'Vitória', 'loss' => 'Derrota', 'draw' => 'Empate'][$row['result']], $row['players'], $row['context'] === 'casual' ? 'Casual' : 'Torneio']), ';', '"', '');
            }
            fclose($out);
        }, 'minhas-partidas.csv', ['Content-Type' => 'text/csv; charset=UTF-8', 'Cache-Control' => 'no-store']);
    }
}
