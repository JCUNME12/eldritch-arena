<?php

namespace App\Http\Controllers;

use App\Models\Tournament;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class TournamentController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate(['q' => ['nullable', 'string', 'max:100'], 'game' => ['nullable', Rule::in(array_keys(config('cardgames')))], 'format' => ['nullable', 'string', 'max:40'], 'scope' => ['nullable', 'in:upcoming,all,mine']]);
        $tournaments = Tournament::with('organizer')->withCount('registrations')
            ->when($filters['q'] ?? null, fn ($q, $v) => $q->whereLike('title', '%'.$v.'%'))
            ->when($filters['game'] ?? null, fn ($q, $v) => $q->where('game', $v))
            ->when($filters['format'] ?? null, fn ($q, $v) => $q->where('format', $v))
            ->when(($filters['scope'] ?? 'upcoming') === 'upcoming', fn ($q) => $q->whereNull('cancelled_at')->where('starts_at', '>', now()))
            ->when(($filters['scope'] ?? '') === 'mine', fn ($q) => $q->where(fn ($q) => $q->where('organizer_id', auth()->id())->orWhereHas('registrations', fn ($q) => $q->where('user_id', auth()->id()))))
            ->orderBy('starts_at')->paginate(12)->withQueryString();

        return view('tournaments.index', compact('tournaments'));
    }

    public function show(Tournament $tournament): View
    {
        $tournament->load(['organizer', 'registrations.user'])->loadCount('registrations');

        return view('tournaments.show', compact('tournament'));
    }

    public function create(): View
    {
        return view('tournaments.create', ['tournament' => new Tournament]);
    }

    public function edit(Request $request, Tournament $tournament): View
    {
        abort_unless($request->user()->id === $tournament->organizer_id, 403);

        return view('tournaments.create', compact('tournament'));
    }

    private function data(Request $request): array
    {
        if ($request->input('game') === 'Magic') {
            $request->merge(['game' => 'Magic: The Gathering']);
        }
        $games = config('cardgames');
        $game = $request->input('game');
        $formats = is_string($game) ? ($games[$game] ?? []) : [];
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'], 'game' => ['required', 'string', Rule::in(array_keys($games))],
            'format' => ['required', 'string', Rule::in($formats)],
            'starts_at' => ['required', 'date'], 'prize' => ['required', 'string', 'max:255'],
            'entry_fee' => ['required', 'numeric', 'min:0', 'max:999999.99'], 'slots' => ['required', 'integer', 'min:2', 'max:256'],
            'location' => ['required', 'string', 'max:255'], 'description' => ['nullable', 'string', 'max:5000'],
        ]);
        $data['starts_at'] = Carbon::parse($data['starts_at'], 'America/Sao_Paulo')->timezone(config('app.timezone'));
        if ($data['starts_at']->isPast()) {
            throw ValidationException::withMessages(['starts_at' => 'Escolha uma data futura (horário de Brasília).']);
        }

        return $data;
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->data($request);
        $data['organizer_id'] = $request->user()->id;
        $data['highlighted'] = false;
        $tournament = Tournament::create($data);

        return redirect()->route('tournaments.show', $tournament)->with('status', 'Torneio publicado.');
    }

    public function update(Request $request, Tournament $tournament): RedirectResponse
    {
        abort_unless($request->user()->id === $tournament->organizer_id, 403);
        $data = $this->data($request);
        DB::transaction(function () use ($tournament, $data) {
            $locked = Tournament::lockForUpdate()->findOrFail($tournament->id);
            if ($locked->cancelled_at || $locked->starts_at->isPast()) {
                throw ValidationException::withMessages(['tournament' => 'Este torneio não está aberto para edição.']);
            }
            if ($data['slots'] < $locked->registrations()->count()) {
                throw ValidationException::withMessages(['slots' => 'As vagas não podem ser menores que o número de inscritos.']);
            }
            $locked->update($data);
        });

        return redirect()->route('tournaments.show', $tournament)->with('status', 'Torneio atualizado.');
    }

    public function cancel(Request $request, Tournament $tournament): RedirectResponse
    {
        abort_unless($request->user()->id === $tournament->organizer_id, 403);
        DB::transaction(fn () => Tournament::lockForUpdate()->findOrFail($tournament->id)->update(['cancelled_at' => now()]));

        return back()->with('status', 'Torneio cancelado. O histórico dos inscritos foi preservado.');
    }

    public function register(Request $request, Tournament $tournament): RedirectResponse
    {
        DB::transaction(function () use ($request, $tournament) {
            $t = Tournament::lockForUpdate()->findOrFail($tournament->id);
            if ($t->cancelled_at || $t->starts_at->isPast()) {
                throw ValidationException::withMessages(['tournament' => 'As inscrições deste torneio estão encerradas.']);
            }
            if ($t->organizer_id === $request->user()->id) {
                throw ValidationException::withMessages(['tournament' => 'Você já organiza este torneio.']);
            }
            if ($t->isUserRegistered($request->user())) {
                return;
            }
            if ($t->registrations()->count() >= $t->slots) {
                throw ValidationException::withMessages(['tournament' => 'Este torneio está lotado.']);
            }
            $t->registrations()->create(['user_id' => $request->user()->id, 'status' => 'confirmed']);
        });

        return back()->with('status', 'Sua inscrição está confirmada.');
    }

    public function unregister(Request $request, Tournament $tournament): RedirectResponse
    {
        DB::transaction(function () use ($request, $tournament) {
            $t = Tournament::lockForUpdate()->findOrFail($tournament->id);
            if ($t->starts_at->isPast() && ! $t->cancelled_at) {
                throw ValidationException::withMessages(['tournament' => 'O torneio já começou. Procure o organizador.']);
            }
            $t->registrations()->where('user_id', $request->user()->id)->delete();
        });

        return back()->with('status', 'Inscrição cancelada.');
    }
}
