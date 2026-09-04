<x-layouts.app title="Eldritch Arena — Comunidade brasileira de TCG">
    <section class="grid min-h-[78vh] items-center gap-10 py-10 lg:grid-cols-[1.05fr_.95fr]">
        <div>
            <div class="mb-6 inline-flex rounded-full border border-arena-purple/40 bg-arena-purple/10 px-4 py-2 text-sm font-bold text-purple-100 shadow-neon">Torneios, cartas e comunidade em um só lugar</div>
            <h1 class="font-display text-5xl font-black leading-tight tracking-tight md:text-7xl"><span class="arena-glow-text">Eldritch Arena</span></h1>
            <p class="mt-5 text-2xl font-extrabold text-white md:text-3xl">Sua próxima partida começa aqui.</p>
            <p class="mt-5 max-w-2xl text-lg leading-8 text-slate-300">Encontre torneios, anuncie cartas, converse com outros jogadores e use ferramentas criadas para Magic: The Gathering, Pokémon, Yu-Gi-Oh! e outros card games.</p>
            <div class="mt-8 flex flex-col gap-3 sm:flex-row">
                @auth
                    <a href="{{ route('dashboard') }}" class="arena-btn text-base">Acessar minha arena</a>
                @else
                    <a href="{{ route('register') }}" class="arena-btn text-base">Criar minha conta</a>
                    <a href="{{ route('login') }}" class="arena-btn-secondary text-base">Já tenho uma conta</a>
                @endauth
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
</x-layouts.app>
