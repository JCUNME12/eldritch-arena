<?php

namespace App\Http\Controllers;

use App\Models\CardListing;
use App\Models\CommunityTopic;
use App\Models\Tournament;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function __invoke(Request $request): View
    {
        if ($user = $request->user()) {
            return view('welcome', ['home' => [
                'firstName' => explode(' ', trim($user->name))[0],
                'role' => match (true) {
                    $user->isAdmin() => 'Administrador',
                    $user->isOrganizer() => 'Loja / Organizador',
                    default => 'Jogador',
                },
                'summary' => [
                    'registrations' => $user->registrations()->count(),
                    'listings' => $user->cardListings()->count(),
                    'topics' => $user->communityTopics()->count(),
                    'tournaments' => $user->tournaments()->count(),
                ],
                'tournaments' => Tournament::query()
                    ->where('status', Tournament::STATUS_PUBLISHED)
                    ->where('starts_at', '>', now())
                    ->withCount('registrations')
                    ->orderBy('starts_at')
                    ->limit(3)
                    ->get(),
                'listings' => CardListing::query()
                    ->latest()
                    ->limit(3)
                    ->get(),
                'topics' => CommunityTopic::query()
                    ->with('user')
                    ->withCount(['comments', 'reactions'])
                    ->orderByDesc('is_pinned')
                    ->latest()
                    ->limit(3)
                    ->get(),
            ]]);
        }

        return view('welcome', [
            'home' => null,
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
