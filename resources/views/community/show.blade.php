<x-layouts.app title="{{ $topic->title }} — Comunidade">
    @php
        $reactionSummary = $topic->reactions->groupBy('type')->map->count();
    @endphp

    <div class="mb-6 flex flex-col gap-3 md:flex-row md:items-end md:justify-between">
        <div>
            <a href="{{ route('community') }}" class="text-sm font-bold text-arena-cyan hover:text-white">← Voltar para comunidade</a>
            <div class="mt-3 flex flex-wrap items-center gap-2">
                @if($topic->is_pinned)
                    <span class="rounded-full border border-arena-gold/40 bg-arena-gold/10 px-3 py-1 text-xs font-black uppercase tracking-wide text-arena-gold">Fixado</span>
                @endif
                <span class="rounded-full border border-arena-purple/40 bg-arena-purple/10 px-3 py-1 text-xs font-bold uppercase tracking-wide text-purple-100">{{ $topic->category }}</span>
                <span class="text-sm text-slate-500">Por {{ $topic->user?->name ?? 'Comunidade' }} • {{ $topic->created_at->format('d/m/Y H:i') }}</span>
                @if($topic->updated_at->gt($topic->created_at->copy()->addMinute()))
                    <span class="text-xs text-slate-500">editado</span>
                @endif
            </div>
            <h1 class="arena-section-title mt-3">{{ $topic->title }}</h1>
        </div>
        <div class="flex flex-wrap gap-2">
            @if(auth()->id() === $topic->user_id)
                <a href="{{ route('community.edit', $topic) }}" class="arena-btn-secondary">Editar</a>
                <form method="POST" action="{{ route('community.destroy', $topic) }}" onsubmit="return confirm('Excluir este tópico e todos os comentários dele?')">
                    @csrf
                    @method('DELETE')
                    <button class="arena-btn-secondary border-red-400/40 text-red-200 hover:bg-red-500/10" type="submit">Excluir</button>
                </form>
            @endif
            <a href="{{ route('community.create') }}" class="arena-btn">Novo tópico</a>
        </div>
    </div>

    @if(session('success'))
        <div class="mb-5 rounded-2xl border border-emerald-400/30 bg-emerald-500/10 p-4 text-sm font-bold text-emerald-200">
            {{ session('success') }}
        </div>
    @endif

    <article class="arena-card p-6">
        <p class="whitespace-pre-line text-slate-300">{{ $topic->body }}</p>

        @if($topic->image_path)
            <div class="mt-5 overflow-hidden rounded-2xl border border-white/10 bg-black/30">
                <img src="{{ asset('storage/' . $topic->image_path) }}" alt="Imagem do tópico {{ $topic->title }}" class="max-h-[520px] w-full object-contain">
            </div>
        @endif

        <div class="mt-6 border-t border-white/10 pt-5">
            <p class="mb-3 text-sm font-black uppercase tracking-wide text-slate-400">Reações ao tópico</p>
            <div class="flex flex-wrap gap-2">
                @foreach($reactionTypes as $type => $emoji)
                    @php
                        $count = $reactionSummary[$type] ?? 0;
                        $reacted = $topic->reactions->where('user_id', auth()->id())->where('type', $type)->isNotEmpty();
                    @endphp
                    <form method="POST" action="{{ route('community.react', $topic) }}">
                        @csrf
                        <input type="hidden" name="type" value="{{ $type }}">
                        <button type="submit" class="rounded-full border px-4 py-2 text-sm font-bold transition {{ $reacted ? 'border-arena-cyan bg-arena-cyan/15 text-white' : 'border-white/10 bg-white/5 text-slate-300 hover:border-arena-cyan/50 hover:text-white' }}">
                            {{ $emoji }} {{ $count }}
                        </button>
                    </form>
                @endforeach
            </div>
        </div>
    </article>

    <section class="mt-6 grid gap-4">
        <div class="flex items-center justify-between">
            <h2 class="text-2xl font-black text-white">Comentários</h2>
            <span class="text-sm text-slate-500">{{ $topic->comments->count() }} resposta(s)</span>
        </div>

        <form method="POST" action="{{ route('community.comment', $topic) }}" enctype="multipart/form-data" class="arena-card grid gap-4 p-5">
            @csrf
            <label class="grid gap-2">
                <span class="text-sm font-bold text-slate-200">Adicionar comentário</span>
                <textarea name="body" rows="4" required style="background-color: #050816 !important; color: #ffffff !important; caret-color: #ffffff !important;" class="rounded-2xl border border-white/10 px-4 py-3 placeholder:text-slate-500 outline-none ring-1 ring-white/10 focus:border-arena-cyan focus:ring-arena-cyan/50" placeholder="Escreva sua resposta para a comunidade...">{{ old('body') }}</textarea>
                @error('body') <span class="text-sm text-red-300">{{ $message }}</span> @enderror
            </label>
            <label class="grid gap-2">
                <span class="text-sm font-bold text-slate-200">Imagem opcional no comentário</span>
                <input type="file" name="image" accept="image/*" style="background-color: #050816 !important; color: #ffffff !important;" class="rounded-2xl border border-white/10 px-4 py-3 text-sm text-slate-300 file:mr-4 file:rounded-full file:border-0 file:bg-arena-cyan file:px-4 file:py-2 file:text-sm file:font-black file:text-slate-950 hover:file:bg-white">
                @error('image') <span class="text-sm text-red-300">{{ $message }}</span> @enderror
            </label>
            <button class="arena-btn w-fit" type="submit">Comentar</button>
        </form>

        @forelse($topic->comments as $comment)
            <article class="arena-card p-5">
                <div class="flex flex-wrap items-start justify-between gap-3">
                    <div class="flex flex-wrap items-center gap-2 text-sm">
                        <span class="font-bold text-white">{{ $comment->user?->name ?? 'Usuário' }}</span>
                        @if($comment->user?->isPremium())
                            <span class="rounded-full border border-arena-gold/40 bg-arena-gold/10 px-2 py-0.5 text-xs font-black uppercase text-arena-gold">Premium</span>
                        @endif
                        <span class="text-slate-500">{{ $comment->created_at->diffForHumans() }}</span>
                        @if($comment->updated_at->gt($comment->created_at->copy()->addMinute()))
                            <span class="text-xs text-slate-500">editado</span>
                        @endif
                    </div>

                    @if(auth()->id() === $comment->user_id)
                        <div class="flex flex-wrap gap-2">
                            <a href="{{ route('community.comments.edit', $comment) }}" class="rounded-full border border-arena-cyan/30 px-3 py-1 text-xs font-bold text-arena-cyan hover:bg-arena-cyan/10">Editar</a>
                            <form method="POST" action="{{ route('community.comments.destroy', $comment) }}" onsubmit="return confirm('Excluir este comentário?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="rounded-full border border-red-400/30 px-3 py-1 text-xs font-bold text-red-300 hover:bg-red-500/10">Excluir</button>
                            </form>
                        </div>
                    @endif
                </div>

                <p class="mt-3 whitespace-pre-line text-slate-300">{{ $comment->body }}</p>

                @if($comment->image_path)
                    <div class="mt-4 overflow-hidden rounded-2xl border border-white/10 bg-black/30">
                        <img src="{{ asset('storage/' . $comment->image_path) }}" alt="Imagem do comentário" class="max-h-96 w-full object-contain">
                    </div>
                @endif
            </article>
        @empty
            <div class="arena-card p-6 text-center text-slate-400">Ainda não há comentários. Seja o primeiro a responder.</div>
        @endforelse
    </section>
</x-layouts.app>
