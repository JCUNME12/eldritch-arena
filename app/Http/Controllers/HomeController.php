<?php

namespace App\Http\Controllers;

use App\Models\CardListing;
use App\Models\Tournament;
use App\Models\User;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function __invoke(): View
    {
        return view('welcome', [
            'nextTournament' => Tournament::query()
                ->where('status', Tournament::STATUS_PUBLISHED)
                ->where('starts_at', '>', now())
                ->withCount('registrations')
                ->orderBy('starts_at')
                ->first(),
            'stats' => [
                'community' => User::where('is_admin', false)->count(),
                'tournaments' => Tournament::query()
                    ->where('status', Tournament::STATUS_PUBLISHED)
                    ->where('starts_at', '>', now())
                    ->count(),
                'listings' => CardListing::count(),
            ],
        ]);
    }
}
