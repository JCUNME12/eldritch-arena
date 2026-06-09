<nav class="relative z-20 border-b border-white/10 bg-black/25 backdrop-blur-xl">
    <div class="mx-auto flex max-w-7xl items-center justify-between px-4 py-4 sm:px-6 lg:px-8">
        <a href="{{ route('home') }}" class="flex items-center gap-3">
            <div class="grid h-11 w-11 place-items-center rounded-2xl bg-gradient-to-br from-arena-purple to-arena-cyan shadow-neon">⚔</div>
            <div>
                <p class="font-display text-lg font-black tracking-wide text-white">Eldritch Arena</p>
                <p class="hidden text-xs font-semibold text-slate-400 sm:block">TCG hub Brasil</p>
            </div>
        </a>

        <div class="hidden items-center gap-2 md:flex">
            @auth
                <a href="{{ route('dashboard') }}" class="arena-btn-secondary">Dashboard</a>
                <a href="{{ route('tournaments.index') }}" class="arena-btn-secondary">Torneios</a>
                <a href="{{ route('marketplace') }}" class="arena-btn-secondary">Marketplace</a>
                <a href="{{ route('marketplace.create') }}" class="arena-btn-secondary">Vender Carta</a>
                <a href="{{ route('community') }}" class="arena-btn-secondary">Comunidade</a>
                <a href="{{ route('premium') }}" class="arena-btn-secondary">Premium</a>
                <a href="{{ route('life-counter') }}" class="arena-btn">Contador</a>
                <form method="POST" action="{{ route('logout') }}">@csrf<button class="arena-btn-secondary">Sair</button></form>
            @else
                <a href="{{ route('login') }}" class="arena-btn-secondary">Entrar</a>
                <a href="{{ route('register') }}" class="arena-btn">Entrar na Arena</a>
            @endauth
        </div>
    </div>
</nav>
