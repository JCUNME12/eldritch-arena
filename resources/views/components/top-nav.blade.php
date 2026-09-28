<nav class="relative z-40 border-b border-white/10 bg-arena-black" x-data="{open:false}" aria-label="Navegação principal">
<div class="mx-auto flex max-w-7xl items-center justify-between gap-4 px-4 py-4 sm:px-6">
<a href="{{ route('home') }}" class="flex items-center gap-3">
<span class="brand-mark" aria-hidden="true">E</span>
<span class="text-lg font-bold tracking-tight">Eldritch <span class="text-arena-gold">Arena</span>
</span>
</a>
<div class="hidden lg:flex items-center gap-1">@auth
@foreach(['dashboard'=>'Meu painel','tournaments.index'=>'Torneios','marketplace'=>'Cartas','community'=>'Comunidade','life-counter'=>'Mesa de jogo'] as $route=>$label)<a class="nav-link {{ request()->routeIs($route) ? 'active' : '' }}" href="{{ route($route) }}">{{ $label }}</a>
@endforeach<form method="POST" action="{{ route('logout') }}">@csrf<button class="nav-link">Sair</button>
</form>@else<a class="nav-link" href="{{ route('login') }}">Entrar</a>
<a class="arena-btn" href="{{ route('register') }}">Criar conta</a>@endauth</div>
<button class="arena-btn-secondary lg:hidden" @click="open=!open" :aria-expanded="open" aria-controls="mobile-menu">Menu</button>
</div>
<div id="mobile-menu" class="border-t border-white/10 p-4 lg:hidden" x-show="open" x-cloak>@auth<div class="grid gap-2">
@foreach(['dashboard'=>'Meu painel','tournaments.index'=>'Torneios','marketplace'=>'Cartas','marketplace.create'=>'Anunciar carta','community'=>'Comunidade','life-counter'=>'Mesa de jogo','premium'=>'Arena Plus'] as $route=>$label)<a class="nav-link" href="{{ route($route) }}">{{ $label }}</a>
@endforeach<form method="POST" action="{{ route('logout') }}">@csrf<button class="nav-link">Sair da conta</button>
</form>
</div>@else<div class="flex gap-3">
<a class="arena-btn-secondary" href="{{ route('login') }}">Entrar</a>
<a class="arena-btn" href="{{ route('register') }}">Criar conta</a>
</div>@endauth</div>
</nav>
