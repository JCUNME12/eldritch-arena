<nav class="fixed bottom-3 left-3 right-3 z-30 rounded-3xl border border-white/10 bg-black/70 p-2 shadow-neon backdrop-blur-xl md:hidden">
    <div class="grid grid-cols-4 gap-1">
        <a href="{{ route('dashboard') }}" class="bottom-nav-link {{ request()->routeIs('dashboard') ? 'bottom-nav-link-active' : '' }}"><span>🏰</span><span>Início</span></a>
        <a href="{{ route('tournaments.index') }}" class="bottom-nav-link {{ request()->routeIs('tournaments.*') ? 'bottom-nav-link-active' : '' }}"><span>🏆</span><span>Torneios</span></a>
        <a href="{{ route('marketplace') }}" class="bottom-nav-link {{ request()->routeIs('marketplace') ? 'bottom-nav-link-active' : '' }}"><span>🃏</span><span>Cartas</span></a>
        <a href="{{ route('life-counter') }}" class="bottom-nav-link {{ request()->routeIs('life-counter') ? 'bottom-nav-link-active' : '' }}"><span>❤️</span><span>Vida</span></a>
    </div>
</nav>
