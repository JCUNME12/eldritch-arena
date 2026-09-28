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
        $filters = $request->validate(['q' => ['nullable', 'string', 'max:100'], 'game' => ['nullable', 'in:Magic,Pokémon,Yu-Gi-Oh'], 'sort' => ['nullable', 'in:recent,price_asc,price_desc'], 'mine' => ['nullable', 'in:1']]);
        $game = $filters['game'] ?? null;

        $cards = CardListing::query()
            ->when($game, fn ($query) => $query->where('game', $game))
            ->when($filters['q'] ?? null, fn ($q, $v) => $q->whereLike('name', '%'.$v.'%'))
            ->when($filters['mine'] ?? null, fn ($q) => $q->where('user_id', $request->user()->id))
            ->when(($filters['sort'] ?? '') === 'price_asc', fn ($q) => $q->orderBy('price'))
            ->when(($filters['sort'] ?? '') === 'price_desc', fn ($q) => $q->orderByDesc('price'))
            ->when(empty($filters['sort']), fn ($q) => $q->orderByDesc('highlighted'))
            ->latest()->paginate(12)->withQueryString();

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
            'rarities' => ['Comum', 'Incomum', 'Rara', 'Mítica', 'Ultra Rara', 'Secreta', 'Promo'],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'game' => ['required', 'in:Magic,Pokémon,Yu-Gi-Oh'],
            'edition' => ['nullable', 'string', 'max:120'],
            'rarity' => ['required', 'in:Comum,Incomum,Rara,Mítica,Ultra Rara,Secreta,Promo'],
            'condition' => ['required', 'in:Novo,Excelente,Bom,Usado,Danificado'],
            'description' => ['nullable', 'string', 'max:1000'],
            'price' => ['required', 'numeric', 'min:0', 'max:999999.99'],
            'image_url' => ['nullable', 'url:http,https', 'max:500'],
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

    public function edit(Request $request, CardListing $cardListing): View
    {
        abort_unless($cardListing->user_id === $request->user()->id, 403);

        return view('marketplace.edit', ['card' => $cardListing]);
    }

    public function update(Request $request, CardListing $cardListing): RedirectResponse
    {
        abort_unless($cardListing->user_id === $request->user()->id, 403);
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'], 'price' => ['required', 'numeric', 'min:0', 'max:999999.99'],
            'description' => ['nullable', 'string', 'max:1000'], 'edition' => ['nullable', 'string', 'max:120'],
            'condition' => ['required', 'in:Novo,Excelente,Bom,Usado,Danificado'],
            'rarity' => ['required', 'in:Comum,Incomum,Rara,Mítica,Ultra Rara,Secreta,Promo'],
            'image_url' => ['nullable', 'url:http,https', 'max:500'],
        ]);
        $cardListing->update($data);

        return redirect()->route('marketplace.show', $cardListing)->with('status', 'Anúncio atualizado.');
    }

    public function destroy(Request $request, CardListing $cardListing): RedirectResponse
    {
        abort_unless($cardListing->user_id === $request->user()->id, 403);
        $cardListing->delete();

        return redirect()->route('marketplace')->with('status', 'Anúncio removido.');
    }

    public function show(CardListing $cardListing): View
    {
        return view('marketplace.show', [
            'card' => $cardListing,
        ]);
    }
}
