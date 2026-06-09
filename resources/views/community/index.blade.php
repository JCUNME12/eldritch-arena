<x-layouts.app title="Comunidade — Eldritch Arena">
    <div class="flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
        <div>
            <p class="font-bold text-arena-cyan">Comunidade TCG</p>
            <h1 class="arena-section-title">Comunidade</h1>
            <p class="mt-2 max-w-3xl text-slate-400">Fórum do Eldritch Arena para players, lojistas e organizadores trocarem estratégias, dúvidas, anúncios e experiências de torneio.</p>
        </div>
        <a href="{{ route('community.create') }}" class="arena-btn">Criar novo tópico</a>
    </div>

    @if(session('success'))
        <div class="mt-5 rounded-2xl border border-emerald-400/30 bg-emerald-500/10 p-4 text-sm font-bold text-emerald-200">
            {{ session('success') }}
        </div>
    @endif

    <div class="mt-6 grid gap-5 lg:grid-cols-[1.3fr_.7fr]">
        <section class="grid gap-4">
            @forelse($topics as $topic)
                @php
                    $reactionSummary = $topic->reactions->groupBy('type')->map->count();
                @endphp
                <article class="arena-card p-6 transition hover:border-arena-cyan/40">
                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <div class="flex flex-wrap items-center gap-2">
                            @if($topic->is_pinned)
                                <span class="rounded-full border border-arena-gold/40 bg-arena-gold/10 px-3 py-1 text-xs font-black uppercase tracking-wide text-arena-gold">Fixado</span>
                            @endif
                            <span class="rounded-full border border-arena-purple/40 bg-arena-purple/10 px-3 py-1 text-xs font-bold uppercase tracking-wide text-purple-100">{{ $topic->category }}</span>
                            <span class="text-sm text-slate-500">Por {{ $topic->user?->name ?? 'Comunidade' }} • {{ $topic->created_at->diffForHumans() }}</span>
                        </div>

                        @if(auth()->id() === $topic->user_id)
                            <div class="flex flex-wrap gap-2">
                                <a href="{{ route('community.edit', $topic) }}" class="rounded-full border border-arena-cyan/30 px-3 py-1 text-xs font-bold text-arena-cyan hover:bg-arena-cyan/10">Editar</a>
                                <form method="POST" action="{{ route('community.destroy', $topic) }}" onsubmit="return confirm('Excluir este tópico e todos os comentários dele?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="rounded-full border border-red-400/30 px-3 py-1 text-xs font-bold text-red-300 hover:bg-red-500/10">Excluir</button>
                                </form>
                            </div>
                        @endif
                    </div>

                    <a href="{{ route('community.show', $topic) }}" class="mt-3 block text-2xl font-black text-white hover:text-arena-cyan">
                        {{ $topic->title }}
                    </a>

                    @if($topic->image_path)
                        <a href="{{ route('community.show', $topic) }}" class="mt-4 block overflow-hidden rounded-2xl border border-white/10 bg-black/30">
                            <img src="{{ asset('storage/' . $topic->image_path) }}" alt="Imagem do tópico {{ $topic->title }}" class="max-h-64 w-full object-cover">
                        </a>
                    @endif

                    <p class="mt-3 line-clamp-3 text-slate-400">{{ $topic->body }}</p>

                    <div class="mt-4 flex flex-wrap gap-2">
                        @foreach($reactionTypes as $type => $emoji)
                            @if(($reactionSummary[$type] ?? 0) > 0)
                                <span class="rounded-full border border-white/10 bg-white/5 px-3 py-1 text-sm text-slate-200">{{ $emoji }} {{ $reactionSummary[$type] }}</span>
                            @endif
                        @endforeach
                    </div>

                    <div class="mt-5 flex flex-wrap items-center justify-between gap-3 text-sm text-slate-400">
                        <span>{{ $topic->comments_count }} comentário(s) • {{ $topic->reactions->count() }} reação(ões)</span>
                        <a href="{{ route('community.show', $topic) }}" class="arena-btn-secondary">Abrir discussão</a>
                    </div>
                </article>
            @empty
                <div class="arena-card p-8 text-center">
                    <h2 class="text-2xl font-black text-white">Nenhum tópico ainda</h2>
                    <p class="mt-2 text-slate-400">Seja o primeiro a iniciar uma conversa na comunidade.</p>
                    <a href="{{ route('community.create') }}" class="arena-btn mt-5 inline-flex">Criar primeiro tópico</a>
                </div>
            @endforelse
        </section>

        <aside class="grid h-fit gap-4">
            <div class="arena-card p-6">
                <p class="font-bold text-arena-gold">Categorias</p>
                <div class="mt-4 flex flex-wrap gap-2">
                    @foreach($categories as $category)
                        <span class="rounded-full border border-white/10 bg-white/5 px-3 py-1 text-sm text-slate-300">{{ $category }}</span>
                    @endforeach
                </div>
            </div>
            <div class="arena-card p-6">
                <p class="font-bold text-arena-cyan">Como usar</p>
                <div class="mt-4 grid gap-3 text-sm text-slate-300">
                    <p class="rounded-2xl border border-white/10 bg-white/5 p-3">Crie tópicos para pedir ajuda com decks, divulgar eventos ou negociar cartas.</p>
                    <p class="rounded-2xl border border-white/10 bg-white/5 p-3">Anexe imagens para mostrar cartas, prints de decklists ou registros de torneios.</p>
                    <p class="rounded-2xl border border-white/10 bg-white/5 p-3">Reaja com joinha, coração, fogo, ideia ou aplauso para simular engajamento real.</p>
                </div>
            </div>
        </aside>
    </div>
</x-layouts.app>
