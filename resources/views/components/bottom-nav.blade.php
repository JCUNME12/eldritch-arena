<nav class="fixed bottom-0 left-0 right-0 z-30 border-t border-white/10 bg-arena-black px-2 pb-2 lg:hidden" aria-label="Atalhos">
<div class="grid grid-cols-5">
@foreach(['dashboard'=>['⌂','Painel'],'tournaments.index'=>['◇','Torneios'],'life-counter'=>['＋','Mesa'],'marketplace'=>['▤','Cartas'],'community'=>['◎','Comunidade']] as $route=>$item)<a href="{{ route($route) }}" class="bottom-nav-link {{ request()->routeIs($route) ? 'bottom-nav-link-active' : '' }}">
<span class="text-xl" aria-hidden="true">{{ $item[0] }}</span>
<span>{{ $item[1] }}</span>
</a>
@endforeach</div>
</nav>
