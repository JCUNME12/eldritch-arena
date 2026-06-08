<?php

namespace App\Http\Controllers;

use App\Models\Tournament;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TournamentController extends Controller
{
    public function index(): View
    {
        return view('tournaments.index', [
            'tournaments' => Tournament::with(['organizer', 'registrations'])->withCount('registrations')->orderBy('starts_at')->get(),
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
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'game' => ['required', 'string', 'max:80'],
            'starts_at' => ['required', 'date'],
            'prize' => ['required', 'string', 'max:255'],
            'entry_fee' => ['required', 'numeric', 'min:0'],
            'slots' => ['required', 'integer', 'min:2', 'max:256'],
            'location' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
        ]);

        $data['organizer_id'] = $request->user()->id;
        $data['highlighted'] = true;

        $tournament = Tournament::create($data);

        return redirect()->route('tournaments.show', $tournament)->with('status', 'Torneio criado e publicado na Arena.');
    }

    public function register(Request $request, Tournament $tournament): RedirectResponse
    {
        $tournament->registrations()->firstOrCreate([
            'user_id' => $request->user()->id,
        ], ['status' => 'confirmed']);

        return back()->with('status', 'Inscrição confirmada. Prepare seu deck.');
    }
}
