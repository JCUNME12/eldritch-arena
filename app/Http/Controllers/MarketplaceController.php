<?php

namespace App\Http\Controllers;

use App\Models\CardListing;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class MarketplaceController extends Controller
{
    public function index(Request $request): View
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

    public function create(): View
    {
        return view('marketplace.create', [
            'games' => ['Magic', 'Pokémon', 'Yu-Gi-Oh'],
            'conditions' => ['Novo', 'Excelente', 'Bom', 'Usado', 'Danificado'],
            'rarities' => ['Comum', 'Incomum', 'Rara', 'Mítica', 'Ultra Rara', 'Secreta'],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'game' => ['required', 'string', 'max:40'],
            'edition' => ['nullable', 'string', 'max:120'],
            'rarity' => ['required', 'string', 'max:60'],
            'condition' => ['required', 'string', 'max:60'],
            'description' => ['nullable', 'string', 'max:1000'],
            'price' => ['required', 'numeric', 'min:0', 'max:999999.99'],
            'image_url' => ['nullable', 'url', 'max:500'],
        ]);

        $user = Auth::user();

        $card = CardListing::create([
            ...$validated,
            'user_id' => $user->id,
            'seller_name' => $user->name,
            'seller_type' => $user->isOrganizer() ? 'loja' : 'player',
            'contact_email' => $user->email,
            'highlighted' => $user->isPremium(),
        ]);

        return redirect()
            ->route('marketplace.show', $card)
            ->with('status', 'Carta cadastrada no marketplace com sucesso.');
    }

    public function show(CardListing $cardListing): View
    {
        return view('marketplace.show', [
            'card' => $cardListing,
        ]);
    }
}
