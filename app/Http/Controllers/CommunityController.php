<?php

namespace App\Http\Controllers;

use App\Models\CommunityComment;
use App\Models\CommunityReaction;
use App\Models\CommunityTopic;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class CommunityController extends Controller
{
    private array $categories = ['Discussão', 'Decks', 'Marketplace', 'Torneios', 'Dúvidas', 'Avisos'];

    private array $reactionTypes = [
        'like' => '👍',
        'heart' => '❤️',
        'fire' => '🔥',
        'idea' => '💡',
        'clap' => '👏',
    ];

    public function index(): View
    {
        $topics = CommunityTopic::with(['user', 'comments', 'reactions'])
            ->withCount('comments')
            ->orderByDesc('is_pinned')
            ->latest()
            ->get();

        $categories = $this->categories;
        $reactionTypes = $this->reactionTypes;

        return view('community.index', compact('topics', 'categories', 'reactionTypes'));
    }

    public function create(): View
    {
        $categories = $this->categories;

        return view('community.create', compact('categories'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:140'],
            'category' => ['required', 'string', 'max:40'],
            'body' => ['required', 'string', 'min:20', 'max:5000'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp,gif', 'max:4096'],
        ], [
            'title.required' => 'Informe o título do tópico.',
            'body.required' => 'Escreva o conteúdo do tópico.',
            'body.min' => 'O tópico precisa ter pelo menos 20 caracteres.',
            'image.image' => 'Envie um arquivo de imagem válido.',
            'image.max' => 'A imagem pode ter no máximo 4 MB.',
        ]);

        $imagePath = $request->hasFile('image')
            ? $request->file('image')->store('community', 'public')
            : null;

        $topic = CommunityTopic::create([
            'title' => $validated['title'],
            'category' => $validated['category'],
            'body' => $validated['body'],
            'image_path' => $imagePath,
            'user_id' => $request->user()->id,
            'is_pinned' => false,
        ]);

        return redirect()
            ->route('community.show', $topic)
            ->with('success', 'Tópico criado com sucesso na comunidade.');
    }

    public function show(CommunityTopic $topic): View
    {
        $topic->load(['user', 'comments.user', 'reactions.user']);
        $reactionTypes = $this->reactionTypes;

        return view('community.show', compact('topic', 'reactionTypes'));
    }

    public function edit(CommunityTopic $topic): View
    {
        abort_unless(auth()->id() === $topic->user_id, 403);

        $categories = $this->categories;

        return view('community.edit', compact('topic', 'categories'));
    }

    public function update(Request $request, CommunityTopic $topic): RedirectResponse
    {
        abort_unless($request->user()->id === $topic->user_id, 403);

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:140'],
            'category' => ['required', 'string', 'max:40'],
            'body' => ['required', 'string', 'min:20', 'max:5000'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp,gif', 'max:4096'],
            'remove_image' => ['nullable', 'boolean'],
        ], [
            'title.required' => 'Informe o título do tópico.',
            'body.required' => 'Escreva o conteúdo do tópico.',
            'body.min' => 'O tópico precisa ter pelo menos 20 caracteres.',
            'image.image' => 'Envie um arquivo de imagem válido.',
            'image.max' => 'A imagem pode ter no máximo 4 MB.',
        ]);

        if ($request->boolean('remove_image') && $topic->image_path) {
            Storage::disk('public')->delete($topic->image_path);
            $topic->image_path = null;
        }

        if ($request->hasFile('image')) {
            if ($topic->image_path) {
                Storage::disk('public')->delete($topic->image_path);
            }

            $topic->image_path = $request->file('image')->store('community', 'public');
        }

        $topic->title = $validated['title'];
        $topic->category = $validated['category'];
        $topic->body = $validated['body'];
        $topic->save();

        return redirect()
            ->route('community.show', $topic)
            ->with('success', 'Tópico atualizado com sucesso.');
    }

    public function destroy(Request $request, CommunityTopic $topic): RedirectResponse
    {
        abort_unless($request->user()->id === $topic->user_id, 403);

        if ($topic->image_path) {
            Storage::disk('public')->delete($topic->image_path);
        }

        foreach ($topic->comments as $comment) {
            if ($comment->image_path) {
                Storage::disk('public')->delete($comment->image_path);
            }
        }

        $topic->delete();

        return redirect()
            ->route('community')
            ->with('success', 'Tópico excluído com sucesso.');
    }

    public function comment(Request $request, CommunityTopic $topic): RedirectResponse
    {
        $validated = $request->validate([
            'body' => ['required', 'string', 'min:3', 'max:2000'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp,gif', 'max:4096'],
        ], [
            'body.required' => 'Escreva um comentário antes de enviar.',
            'body.min' => 'O comentário precisa ter pelo menos 3 caracteres.',
            'image.image' => 'Envie um arquivo de imagem válido.',
            'image.max' => 'A imagem pode ter no máximo 4 MB.',
        ]);

        $imagePath = $request->hasFile('image')
            ? $request->file('image')->store('community', 'public')
            : null;

        CommunityComment::create([
            'community_topic_id' => $topic->id,
            'user_id' => $request->user()->id,
            'body' => $validated['body'],
            'image_path' => $imagePath,
        ]);

        return redirect()
            ->route('community.show', $topic)
            ->with('success', 'Comentário publicado com sucesso.');
    }

    public function editComment(CommunityComment $comment): View
    {
        abort_unless(auth()->id() === $comment->user_id, 403);

        $comment->load('topic');

        return view('community.edit-comment', compact('comment'));
    }

    public function updateComment(Request $request, CommunityComment $comment): RedirectResponse
    {
        abort_unless($request->user()->id === $comment->user_id, 403);

        $validated = $request->validate([
            'body' => ['required', 'string', 'min:3', 'max:2000'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp,gif', 'max:4096'],
            'remove_image' => ['nullable', 'boolean'],
        ], [
            'body.required' => 'Escreva o comentário antes de salvar.',
            'body.min' => 'O comentário precisa ter pelo menos 3 caracteres.',
            'image.image' => 'Envie um arquivo de imagem válido.',
            'image.max' => 'A imagem pode ter no máximo 4 MB.',
        ]);

        if ($request->boolean('remove_image') && $comment->image_path) {
            Storage::disk('public')->delete($comment->image_path);
            $comment->image_path = null;
        }

        if ($request->hasFile('image')) {
            if ($comment->image_path) {
                Storage::disk('public')->delete($comment->image_path);
            }

            $comment->image_path = $request->file('image')->store('community', 'public');
        }

        $comment->body = $validated['body'];
        $comment->save();

        return redirect()
            ->route('community.show', $comment->topic)
            ->with('success', 'Comentário atualizado com sucesso.');
    }

    public function destroyComment(Request $request, CommunityComment $comment): RedirectResponse
    {
        abort_unless($request->user()->id === $comment->user_id, 403);

        $topic = $comment->topic;

        if ($comment->image_path) {
            Storage::disk('public')->delete($comment->image_path);
        }

        $comment->delete();

        return redirect()
            ->route('community.show', $topic)
            ->with('success', 'Comentário excluído com sucesso.');
    }

    public function react(Request $request, CommunityTopic $topic): RedirectResponse
    {
        $validated = $request->validate([
            'type' => ['required', 'string', 'in:' . implode(',', array_keys($this->reactionTypes))],
        ]);

        $reaction = CommunityReaction::where('community_topic_id', $topic->id)
            ->where('user_id', $request->user()->id)
            ->where('type', $validated['type'])
            ->first();

        if ($reaction) {
            $reaction->delete();
            $message = 'Reação removida.';
        } else {
            CommunityReaction::create([
                'community_topic_id' => $topic->id,
                'user_id' => $request->user()->id,
                'type' => $validated['type'],
            ]);
            $message = 'Reação adicionada.';
        }

        return redirect()
            ->route('community.show', $topic)
            ->with('success', $message);
    }
}
