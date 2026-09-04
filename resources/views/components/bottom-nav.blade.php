<nav class="fixed bottom-3 left-3 right-3 z-30 rounded-3xl border border-white/10 bg-black/70 p-2 shadow-neon backdrop-blur-xl md:hidden">
    <div class="flex gap-1 overflow-x-auto pb-1">
        <a href="{{ route('dashboard') }}" class="bottom-nav-link min-w-20 {{ request()->routeIs('dashboard') ? 'bottom-nav-link-active' : '' }}"><span>🏰</span><span>Início</span></a>
        <a href="{{ route('tournaments.index') }}" class="bottom-nav-link min-w-20 {{ request()->routeIs('tournaments.*') ? 'bottom-nav-link-active' : '' }}"><span>🏆</span><span>Torneios</span></a>
        <a href="{{ route('marketplace') }}" class="bottom-nav-link min-w-20 {{ request()->routeIs('marketplace') || request()->routeIs('marketplace.*') ? 'bottom-nav-link-active' : '' }}"><span>🃏</span><span>Cartas</span></a>
        <a href="{{ route('community') }}" class="bottom-nav-link min-w-20 {{ request()->routeIs('community') ? 'bottom-nav-link-active' : '' }}"><span>👥</span><span>Comunidade</span></a>
        @if(auth()->user()->isAdmin())
            <a href="{{ route('admin.index') }}" class="bottom-nav-link min-w-20 {{ request()->routeIs('admin.*') ? 'bottom-nav-link-active' : '' }}"><span>🛡️</span><span>Admin</span></a>
        @endif
        <a href="{{ route('account.edit') }}" class="bottom-nav-link min-w-20 {{ request()->routeIs('account.*') ? 'bottom-nav-link-active' : '' }}"><span>⚙️</span><span>Conta</span></a>
        <a href="{{ route('life-counter') }}" class="bottom-nav-link min-w-20 {{ request()->routeIs('life-counter') ? 'bottom-nav-link-active' : '' }}"><span>❤️</span><span>Vida</span></a>
    </div>
</nav>
