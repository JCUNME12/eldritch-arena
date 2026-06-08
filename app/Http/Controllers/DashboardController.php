<?php

namespace App\Http\Controllers;

use App\Models\CardListing;
use App\Models\Tournament;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $user = $request->user();

        if ($user->isOrganizer()) {
            return view('dashboard.organizer', [
                'myTournaments' => Tournament::withCount('registrations')->where('organizer_id', $user->id)->latest()->get(),
                'stats' => [
                    'tournaments' => Tournament::where('organizer_id', $user->id)->count(),
                    'registrations' => Tournament::where('organizer_id', $user->id)->withCount('registrations')->get()->sum('registrations_count'),
                    'revenue' => Tournament::where('organizer_id', $user->id)->withCount('registrations')->get()->sum(fn ($t) => $t->registrations_count * $t->entry_fee),
                ],
            ]);
        }

        return view('dashboard.player', [
            'tournaments' => Tournament::withCount('registrations')->where('highlighted', true)->latest()->take(3)->get(),
            'cards' => CardListing::where('highlighted', true)->take(3)->get(),
        ]);
    }
}
