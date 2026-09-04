<x-layouts.app title="Eldritch Arena — Comunidade brasileira de TCG">
    @auth
        <section class="py-8 sm:py-10">
            <div class="arena-card relative overflow-hidden p-6 sm:p-8">
                <div class="absolute -right-24 -top-24 h-72 w-72 rounded-full bg-arena-purple/25 blur-3xl"></div>
                <div class="absolute -bottom-32 left-1/3 h-64 w-64 rounded-full bg-arena-cyan/10 blur-3xl"></div>

                <div class="relative flex flex-col justify-between gap-7 lg:flex-row lg:items-end">
                    <div>
                        <div class="inline-flex items-center gap-2 rounded-full border border-arena-cyan/30 bg-arena-cyan/10 px-4 py-2 text-sm font-bold text-arena-cyan">
                            <span class="h-2 w-2 rounded-full bg-emerald-400 shadow-[0_0_12px_#34d399]"></span>
                            {{ $home['role'] }} conectado
                        </div>
                        <p class="mt-6 text-sm font-black uppercase tracking-[0.24em] text-slate-400">Sua arena</p>
                        <h1 class="mt-2 font-display text-4xl font-black tracking-tight text-white sm:text-6xl">
                            Olá, <span class="arena-glow-text">{{ $home['firstName'] }}</span>
                        </h1>
                        <p class="mt-4 max-w-2xl text-lg leading-8 text-slate-300">Tudo o que você precisa para a próxima partida, torneio ou negociação está reunido aqui.</p>
                    </div>

                    <div class="flex flex-col gap-3 sm:flex-row">
                        @if(auth()->user()->isAdmin())
                            <a href="{{ route('admin.index') }}" class="arena-btn-secondary border-arena-gold/40 text-arena-gold">Painel administrativo</a>
                        @else
                            <a href="{{ route('dashboard') }}" class="arena-btn-secondary">Ver meu painel</a>
                        @endif
                        <a href="{{ route('life-counter') }}" class="arena-btn">Abrir contador de vida</a>
                    </div>
                </div>
            </div>

            <div class="mt-5 grid grid-cols-2 gap-3 lg:grid-cols-4">
                <a href="{{ route('life-counter') }}" class="arena-card p-5 transition hover:-translate-y-1 hover:border-arena-purple/50">
                    <span class="text-2xl">❤️</span>
                    <h2 class="mt-3 font-black text-white">Contador</h2>
                    <p class="mt-1 text-sm text-slate-400">Magic e Yu-Gi-Oh!</p>
                </a>
                <a href="{{ route('tournaments.index') }}" class="arena-card p-5 transition hover:-translate-y-1 hover:border-arena-cyan/50">
                    <span class="text-2xl">🏆</span>
                    <h2 class="mt-3 font-black text-white">Torneios</h2>
                    <p class="mt-1 text-sm text-slate-400">Eventos e inscrições</p>
                </a>
                <a href="{{ route('marketplace') }}" class="arena-card p-5 transition hover:-translate-y-1 hover:border-arena-gold/50">
                    <span class="text-2xl">🃏</span>
                    <h2 class="mt-3 font-black text-white">Marketplace</h2>
                    <p class="mt-1 text-sm text-slate-400">Comprar e anunciar</p>
                </a>
                <a href="{{ route('community') }}" class="arena-card p-5 transition hover:-translate-y-1 hover:border-emerald-400/40">
                    <span class="text-2xl">👥</span>
                    <h2 class="mt-3 font-black text-white">Comunidade</h2>
                    <p class="mt-1 text-sm text-slate-400">Conversas da arena</p>
                </a>
            </div>

            <section class="mt-8">
                <div class="flex items-end justify-between gap-4">
                    <div>
                        <p class="text-sm font-bold text-arena-cyan">Sua atividade</p>
                        <h2 class="mt-1 arena-section-title">Resumo da conta</h2>
                    </div>
                    <a href="{{ route('account.edit') }}" class="text-sm font-bold text-slate-300 transition hover:text-white">Gerenciar conta →</a>
                </div>

                <div class="mt-4 grid grid-cols-2 gap-3 lg:grid-cols-4">
                    <div class="arena-card-soft p-5"><p class="text-sm text-slate-400">Inscrições</p><p class="mt-2 text-4xl font-black text-arena-cyan">{{ $home['summary']['registrations'] }}</p></div>
                    <div class="arena-card-soft p-5"><p class="text-sm text-slate-400">Seus anúncios</p><p class="mt-2 text-4xl font-black text-arena-gold">{{ $home['summary']['listings'] }}</p></div>
                    <div class="arena-card-soft p-5"><p class="text-sm text-slate-400">Seus tópicos</p><p class="mt-2 text-4xl font-black text-purple-300">{{ $home['summary']['topics'] }}</p></div>
                    <div class="arena-card-soft p-5"><p class="text-sm text-slate-400">Torneios criados</p><p class="mt-2 text-4xl font-black text-emerald-300">{{ $home['summary']['tournaments'] }}</p></div>
                </div>
            </section>

            <section class="mt-8 grid gap-5 xl:grid-cols-[1.1fr_.9fr]">
                <div class="arena-card p-6">
                    <div class="flex items-center justify-between gap-4">
                        <div><p class="text-sm font-bold text-arena-cyan">Agenda</p><h2 class="mt-1 text-2xl font-black">Próximos torneios</h2></div>
                        <a href="{{ route('tournaments.index') }}" class="text-sm font-bold text-slate-300 transition hover:text-white">Ver todos →</a>
                    </div>

                    <div class="mt-5 space-y-3">
                        @forelse($home['tournaments'] as $tournament)
                            <a href="{{ route('tournaments.show', $tournament) }}" class="block rounded-2xl border border-white/10 bg-white/5 p-4 transition hover:border-arena-cyan/30 hover:bg-white/10">
                                <div class="flex items-start justify-between gap-4">
                                    <div>
                                        <p class="text-xs font-black uppercase tracking-wider text-arena-cyan">{{ $tournament->game }}</p>
                                        <h3 class="mt-1 font-black text-white">{{ $tournament->title }}</h3>
                                        <p class="mt-2 text-sm text-slate-400">{{ $tournament->starts_at->format('d/m/Y \à\s H:i') }} • {{ $tournament->location }}</p>
                                    </div>
                                    <span class="shrink-0 rounded-full bg-white/10 px-3 py-1 text-xs font-bold text-slate-200">{{ $tournament->registrations_count }}/{{ $tournament->slots }}</span>
                                </div>
                            </a>
                        @empty
                            <div class="rounded-2xl border border-dashed border-white/15 p-6 text-center text-slate-400">Nenhum torneio publicado no momento.</div>
                        @endforelse
                    </div>
                </div>

                <div class="arena-card p-6">
                    <div class="flex items-center justify-between gap-4">
                        <div><p class="text-sm font-bold text-arena-gold">Negociações</p><h2 class="mt-1 text-2xl font-black">Novas cartas</h2></div>
                        <a href="{{ route('marketplace') }}" class="text-sm font-bold text-slate-300 transition hover:text-white">Ver todas →</a>
                    </div>

                    <div class="mt-5 space-y-3">
                        @forelse($home['listings'] as $listing)
                            <a href="{{ route('marketplace.show', $listing) }}" class="flex items-center justify-between gap-4 rounded-2xl border border-white/10 bg-white/5 p-4 transition hover:border-arena-gold/30 hover:bg-white/10">
                                <div class="min-w-0"><p class="truncate font-black text-white">{{ $listing->name }}</p><p class="mt-1 truncate text-sm text-slate-400">{{ $listing->game }} • {{ $listing->condition }}</p></div>
                                <strong class="shrink-0 text-arena-gold">R$ {{ number_format($listing->price, 2, ',', '.') }}</strong>
                            </a>
                        @empty
                            <div class="rounded-2xl border border-dashed border-white/15 p-6 text-center text-slate-400">O marketplace ainda não tem anúncios.</div>
                        @endforelse
                    </div>
                </div>
            </section>

            <section class="mt-5 arena-card p-6">
                <div class="flex items-center justify-between gap-4">
                    <div><p class="text-sm font-bold text-purple-300">Na comunidade</p><h2 class="mt-1 text-2xl font-black">Conversas recentes</h2></div>
                    <a href="{{ route('community') }}" class="text-sm font-bold text-slate-300 transition hover:text-white">Entrar na comunidade →</a>
                </div>

                <div class="mt-5 grid gap-3 md:grid-cols-3">
                    @forelse($home['topics'] as $topic)
                        <a href="{{ route('community.show', $topic) }}" class="rounded-2xl border border-white/10 bg-white/5 p-4 transition hover:border-arena-purple/40 hover:bg-white/10">
                            <div class="flex items-center gap-2 text-xs font-bold uppercase tracking-wider text-purple-300">@if($topic->is_pinned)<span>📌</span>@endif {{ $topic->category }}</div>
                            <h3 class="mt-2 line-clamp-2 font-black text-white">{{ $topic->title }}</h3>
                            <p class="mt-3 text-sm text-slate-400">{{ $topic->user->name }} • {{ $topic->comments_count }} comentários • {{ $topic->reactions_count }} reações</p>
                        </a>
                    @empty
                        <div class="rounded-2xl border border-dashed border-white/15 p-6 text-center text-slate-400 md:col-span-3">Seja a primeira pessoa a iniciar uma conversa.</div>
                    @endforelse
                </div>
            </section>
        </section>
    @else
        <section class="grid min-h-[78vh] items-center gap-10 py-10 lg:grid-cols-[1.05fr_.95fr]">
            <div>
                <div class="mb-6 inline-flex rounded-full border border-arena-purple/40 bg-arena-purple/10 px-4 py-2 text-sm font-bold text-purple-100 shadow-neon">Torneios, cartas e comunidade em um só lugar</div>
                <h1 class="font-display text-5xl font-black leading-tight tracking-tight md:text-7xl"><span class="arena-glow-text">Eldritch Arena</span></h1>
                <p class="mt-5 text-2xl font-extrabold text-white md:text-3xl">Sua próxima partida começa aqui.</p>
                <p class="mt-5 max-w-2xl text-lg leading-8 text-slate-300">Encontre torneios, anuncie cartas, converse com outros jogadores e use ferramentas criadas para Magic: The Gathering, Pokémon, Yu-Gi-Oh! e outros card games.</p>
                <div class="mt-8 flex flex-col gap-3 sm:flex-row">
                    <a href="{{ route('register') }}" class="arena-btn text-base">Criar minha conta</a>
                    <a href="{{ route('login') }}" class="arena-btn-secondary text-base">Já tenho uma conta</a>
                    <a href="#recursos" class="arena-btn-secondary text-base">Conhecer recursos</a>
                </div>
            </div>

            <div class="arena-card relative overflow-hidden p-6">
                <div class="absolute -right-20 -top-20 h-56 w-56 rounded-full bg-arena-purple/30 blur-3xl"></div>
                <div class="relative grid gap-4">
                    <div class="arena-card-soft p-5">
                        <p class="text-sm font-bold text-arena-cyan">Próximo torneio</p>
                        @if($nextTournament)
                            <h2 class="mt-2 text-2xl font-black">{{ $nextTournament->title }}</h2>
                            <p class="mt-2 text-slate-400">{{ $nextTournament->starts_at->format('d/m/Y H:i') }} • {{ $nextTournament->registrations_count }}/{{ $nextTournament->slots }} inscritos</p>
                        @else
                            <h2 class="mt-2 text-2xl font-black">Novos eventos em breve</h2>
                            <p class="mt-2 text-slate-400">Organizadores podem publicar o próximo torneio diretamente pela plataforma.</p>
                        @endif
                    </div>
                    <div class="grid grid-cols-3 gap-3">
                        <div class="arena-card-soft p-4"><p class="text-xs text-slate-400">Comunidade</p><p class="mt-2 text-3xl font-black text-white">{{ $stats['community'] }}</p></div>
                        <div class="arena-card-soft p-4"><p class="text-xs text-slate-400">Torneios</p><p class="mt-2 text-3xl font-black text-arena-cyan">{{ $stats['tournaments'] }}</p></div>
                        <div class="arena-card-soft p-4"><p class="text-xs text-slate-400">Anúncios</p><p class="mt-2 text-3xl font-black text-arena-gold">{{ $stats['listings'] }}</p></div>
                    </div>
                    <div class="rounded-3xl border border-arena-cyan/30 bg-gradient-to-r from-arena-purple/20 to-arena-cyan/10 p-5 shadow-cyan"><p class="font-bold text-white">Contador de vida para suas partidas</p><p class="mt-2 text-sm text-slate-300">Interface rápida e adaptada para celular, pronta para acompanhar cada duelo.</p></div>
                </div>
            </div>
        </section>

        <section id="recursos" class="grid gap-4 py-10 md:grid-cols-3">
            <div class="arena-card p-6"><h3 class="text-xl font-black">🏆 Torneios</h3><p class="mt-3 text-slate-300">Organizadores publicam eventos e jogadores garantem suas inscrições em poucos passos.</p></div>
            <div class="arena-card p-6"><h3 class="text-xl font-black">🃏 Marketplace</h3><p class="mt-3 text-slate-300">Anúncios completos por jogo, raridade, condição e preço, com contato direto com o vendedor.</p></div>
            <div class="arena-card p-6"><h3 class="text-xl font-black">👥 Comunidade</h3><p class="mt-3 text-slate-300">Discussões, imagens, comentários e reações para fortalecer a cena local de card games.</p></div>
        </section>
    @endauth
</x-layouts.app>
