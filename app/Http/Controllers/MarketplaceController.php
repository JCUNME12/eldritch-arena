<?php

namespace App\Http\Controllers;

use App\Models\CardListing;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MarketplaceController extends Controller
{
    public function __invoke(Request $request): View
    {
        $game = $request->query('game');

        $cards = CardListing::query()
            ->when($game, fn ($query) => $query->where('game', $game))
            ->orderByDesc('highlighted')
            ->orderBy('game')
            ->get();

        return view('marketplace.index', [
            'cards' => $cards,
            'selectedGame' => $game,
            'games' => ['Magic', 'Pokémon', 'Yu-Gi-Oh'],
        ]);
    }
}
