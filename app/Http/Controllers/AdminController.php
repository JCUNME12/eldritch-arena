<?php

namespace App\Http\Controllers;

use App\Models\CardListing;
use App\Models\CommunityTopic;
use App\Models\Tournament;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AdminController extends Controller
{
    public function index(): View
    {
        return view('admin.index', [
            'stats' => [
                'users' => User::count(),
                'organizers' => User::where('type', 'organizer')->count(),
                'tournaments' => Tournament::count(),
                'listings' => CardListing::count(),
                'topics' => CommunityTopic::count(),
            ],
            'users' => User::query()->orderByDesc('is_admin')->orderBy('name')->get(),
            'tournaments' => Tournament::query()->with('organizer')->withCount('registrations')->latest()->take(20)->get(),
            'listings' => CardListing::query()->with('user')->latest()->take(20)->get(),
            'topics' => CommunityTopic::query()->with('user')->withCount('comments')->latest()->take(20)->get(),
        ]);
    }

    public function updateUser(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'type' => ['required', Rule::in(['player', 'organizer'])],
            'is_admin' => ['required', 'boolean'],
            'premium_active' => ['required', 'boolean'],
        ]);

        if ($request->user()->is($user) && ! $request->boolean('is_admin')) {
            throw ValidationException::withMessages([
                'is_admin' => 'Você não pode remover o próprio acesso administrativo.',
            ]);
        }

        $user->update([
            'type' => $validated['type'],
            'is_admin' => $request->boolean('is_admin'),
            'premium_active' => $request->boolean('premium_active'),
            'premium_plan' => $request->boolean('premium_active')
                ? ($validated['type'] === 'organizer' ? 'loja_premium' : 'player_premium')
                : 'free',
            'premium_started_at' => $request->boolean('premium_active') ? ($user->premium_started_at ?? now()) : null,
        ]);

        return back()->with('status', "Permissões de {$user->name} atualizadas.");
    }

    public function updateTournament(Request $request, Tournament $tournament): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', Rule::in([
                Tournament::STATUS_PUBLISHED,
                Tournament::STATUS_CANCELLED,
                Tournament::STATUS_FINISHED,
            ])],
            'highlighted' => ['required', 'boolean'],
        ]);

        $tournament->update([
            'status' => $validated['status'],
            'highlighted' => $request->boolean('highlighted'),
        ]);

        return back()->with('status', "Torneio {$tournament->title} atualizado.");
    }

    public function destroyListing(CardListing $cardListing): RedirectResponse
    {
        $name = $cardListing->name;
        $cardListing->delete();

        return back()->with('status', "Anúncio {$name} removido.");
    }

    public function updateTopic(Request $request, CommunityTopic $topic): RedirectResponse
    {
        $request->validate(['is_pinned' => ['required', 'boolean']]);
        $topic->update(['is_pinned' => $request->boolean('is_pinned')]);

        return back()->with('status', "Tópico {$topic->title} atualizado.");
    }

    public function destroyTopic(CommunityTopic $topic): RedirectResponse
    {
        if ($topic->image_path) {
            Storage::disk('public')->delete($topic->image_path);
        }

        foreach ($topic->comments as $comment) {
            if ($comment->image_path) {
                Storage::disk('public')->delete($comment->image_path);
            }
        }

        $title = $topic->title;
        $topic->delete();

        return back()->with('status', "Tópico {$title} removido.");
    }
}
