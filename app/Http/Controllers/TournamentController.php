<?php

namespace App\Http\Controllers;

use App\Models\Tournament;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class TournamentController extends Controller
{
    public function index(): View
    {
        return view('tournaments.index', [
            'tournaments' => Tournament::with(['organizer', 'registrations'])
                ->withCount('registrations')
                ->where('status', Tournament::STATUS_PUBLISHED)
                ->orderBy('starts_at')
                ->get(),
        ]);
    }

    public function show(Tournament $tournament): View
    {
        $tournament->load(['organizer', 'registrations'])->loadCount('registrations');

        return view('tournaments.show', compact('tournament'));
    }

    public function create(): View
    {
        return view('tournaments.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validateTournament($request);

        $data['organizer_id'] = $request->user()->id;
        $data['highlighted'] = $request->user()->isPremium();
        $data['status'] = Tournament::STATUS_PUBLISHED;

        $tournament = Tournament::create($data);

        return redirect()->route('tournaments.show', $tournament)->with('status', 'Torneio criado e publicado na Arena.');
    }

    public function edit(Request $request, Tournament $tournament): View
    {
        $this->authorizeOrganizer($request, $tournament);

        return view('tournaments.edit', compact('tournament'));
    }

    public function update(Request $request, Tournament $tournament): RedirectResponse
    {
        $this->authorizeOrganizer($request, $tournament);
        $tournament->update($this->validateTournament($request));

        return redirect()->route('tournaments.show', $tournament)->with('status', 'Torneio atualizado com sucesso.');
    }

    public function cancel(Request $request, Tournament $tournament): RedirectResponse
    {
        $this->authorizeOrganizer($request, $tournament);
        $tournament->update(['status' => Tournament::STATUS_CANCELLED]);

        return redirect()->route('tournaments.show', $tournament)->with('status', 'Torneio cancelado. Os participantes poderão ver o novo status.');
    }

    public function register(Request $request, Tournament $tournament): RedirectResponse
    {
        $message = DB::transaction(function () use ($request, $tournament): string {
            $lockedTournament = Tournament::query()
                ->whereKey($tournament->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            if ($lockedTournament->registrations()->where('user_id', $request->user()->id)->exists()) {
                return 'Você já está inscrito neste torneio.';
            }

            if (! $lockedTournament->isOpenForRegistration()) {
                throw ValidationException::withMessages([
                    'tournament' => 'As inscrições para este torneio estão encerradas.',
                ]);
            }

            if ($lockedTournament->registrations()->count() >= $lockedTournament->slots) {
                throw ValidationException::withMessages([
                    'tournament' => 'Este torneio atingiu o limite de participantes.',
                ]);
            }

            $lockedTournament->registrations()->create([
                'user_id' => $request->user()->id,
                'status' => 'confirmed',
            ]);

            return 'Inscrição confirmada. Prepare seu deck.';
        }, attempts: 3);

        return back()->with('status', $message);
    }

    public function unregister(Request $request, Tournament $tournament): RedirectResponse
    {
        $deleted = $tournament->registrations()->where('user_id', $request->user()->id)->delete();

        return back()->with('status', $deleted ? 'Sua inscrição foi cancelada.' : 'Você não estava inscrito neste torneio.');
    }

    /** @return array<string, mixed> */
    private function validateTournament(Request $request): array
    {
        return $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'game' => ['required', 'string', 'max:80'],
            'starts_at' => ['required', 'date', 'after:now'],
            'prize' => ['required', 'string', 'max:255'],
            'entry_fee' => ['required', 'numeric', 'min:0', 'max:999999.99'],
            'slots' => ['required', 'integer', 'min:2', 'max:256'],
            'location' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
        ]);
    }

    private function authorizeOrganizer(Request $request, Tournament $tournament): void
    {
        abort_unless(
            $request->user()->isAdmin() || $request->user()->id === $tournament->organizer_id,
            403
        );
    }
}
