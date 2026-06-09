<x-layouts.app title="Eldritch Arena — TCG Hub Brasil">
    <section class="grid min-h-[82vh] items-center gap-10 py-10 lg:grid-cols-[1.05fr_.95fr]">
        <div>
            <div class="mb-6 inline-flex rounded-full border border-arena-purple/40 bg-arena-purple/10 px-4 py-2 text-sm font-bold text-purple-100 shadow-neon">Sua Plataforma De TCG</div>
            <h1 class="font-display text-5xl font-black leading-tight tracking-tight md:text-7xl">
                <span class="arena-glow-text">Eldritch Arena</span>
            </h1>
            <p class="mt-5 text-2xl font-extrabold text-white md:text-3xl">O hub definitivo dos duelos de TCG no Brasil.</p>
            <p class="mt-5 max-w-2xl text-lg leading-8 text-slate-300">A plataforma conecta jogadores, lojas e organizadores de torneios em uma experiência única para Magic: The Gathering, Pokémon, Yu-Gi-Oh! e outros card games. Torneios, marketplace e contador de vida em uma interface imersiva e mobile-first.</p>
            <div class="mt-8 flex flex-col gap-3 sm:flex-row">
                <a href="{{ route('register') }}" class="arena-btn text-base">Entrar na Arena</a>
                <a href="#demo" class="arena-btn-secondary text-base">Ver funcionalidades</a>
            </div>
        </div>
        <div class="arena-card relative overflow-hidden p-6">
            <div class="absolute -right-20 -top-20 h-56 w-56 rounded-full bg-arena-purple/30 blur-3xl"></div>
            <div class="relative grid gap-4">
                <div class="arena-card-soft p-5"><p class="text-sm font-bold text-arena-cyan">Torneio em destaque</p><h3 class="mt-2 text-2xl font-black">Noite Commander Eldritch</h3><p class="mt-2 text-slate-400">32 vagas • premiação exclusiva • inscrição online</p></div>
                <div class="grid grid-cols-2 gap-4">
                    <div class="arena-card-soft p-5"><p class="text-sm text-slate-400">Jogadores</p><p class="mt-2 text-4xl font-black text-white">1.2k</p></div>
                    <div class="arena-card-soft p-5"><p class="text-sm text-slate-400">Eventos/mês</p><p class="mt-2 text-4xl font-black text-arena-cyan">86</p></div>
                </div>
                <div class="rounded-3xl border border-arena-cyan/30 bg-gradient-to-r from-arena-purple/20 to-arena-cyan/10 p-5 shadow-cyan"><p class="font-bold text-white">Contador de vida touch-friendly</p><p class="mt-2 text-sm text-slate-300">Feito para demonstração ao vivo no celular.</p></div>
            </div>
        </div>
    </section>

    <section id="demo" class="grid gap-4 py-10 md:grid-cols-3">
        <div class="arena-card p-6"><h3 class="text-xl font-black">🏆 Torneios</h3><p class="mt-3 text-slate-300">Criação, inscrição e detalhes de eventos para lojas e jogadores.</p></div>
        <div class="arena-card p-6"><h3 class="text-xl font-black">🃏 Marketplace</h3><p class="mt-3 text-slate-300">Cards fake por jogo, raridade, condição e preço para enriquecer a demo.</p></div>
        <div class="arena-card p-6"><h3 class="text-xl font-black">❤️ Contador de Vida</h3><p class="mt-3 text-slate-300">Funcionalidade interativa de destaque para o pitch do TCC.</p></div>
    </section>
</x-layouts.app>
