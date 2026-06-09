<x-layouts.app title="Marketplace — Eldritch Arena">
    <div class="flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
        <div>
            <p class="font-bold text-arena-cyan">Cartas e colecionáveis</p>
            <h1 class="arena-section-title">Marketplace</h1>
            <p class="mt-2 max-w-2xl text-slate-400">Compre, venda e descubra cartas anunciadas por players e lojistas da comunidade Eldritch Arena.</p>
        </div>
        <a href="{{ route('marketplace.create') }}" class="arena-btn">Vender carta</a>
    </div>

    <div class="mt-5 flex flex-wrap gap-2">
        <a href="{{ route('marketplace') }}" class="{{ !$selectedGame ? 'arena-btn' : 'arena-btn-secondary' }}">Todos</a>
        @foreach($games as $game)
            <a href="{{ route('marketplace', ['game' => $game]) }}" class="{{ $selectedGame === $game ? 'arena-btn' : 'arena-btn-secondary' }}">{{ $game }}</a>
        @endforeach
    </div>

    <div class="mt-6 grid gap-4 md:grid-cols-2 lg:grid-cols-3">
        @forelse($cards as $card)
            <a href="{{ route('marketplace.show', $card) }}" class="arena-card group overflow-hidden transition hover:-translate-y-1 hover:border-arena-cyan/50">
                <div class="grid h-40 place-items-center bg-gradient-to-br from-arena-purple/30 via-black/20 to-arena-cyan/20">
                    @if($card->image_url)
                        <img src="{{ $card->image_url }}" alt="{{ $card->name }}" class="h-full w-full object-cover">
                    @else
                        <span class="text-6xl">🃏</span>
                    @endif
                </div>
                <div class="p-5">
                    <div class="flex items-center justify-between gap-2">
                        <p class="text-sm font-bold text-arena-cyan">{{ $card->game }}</p>
                        @if($card->highlighted)
                            <span class="rounded-full border border-arena-gold/40 bg-arena-gold/10 px-2 py-1 text-xs font-bold text-arena-gold">Premium</span>
                        @endif
                    </div>
                    <h2 class="mt-2 text-2xl font-black group-hover:text-arena-cyan">{{ $card->name }}</h2>
                    <p class="mt-2 text-sm text-slate-400">{{ $card->rarity }} • {{ $card->condition }}@if($card->edition) • {{ $card->edition }}@endif</p>
                    @if($card->description)
                        <p class="mt-3 line-clamp-2 text-sm text-slate-300">{{ $card->description }}</p>
                    @endif
                    <div class="mt-4 flex items-end justify-between gap-3">
                        <div>
                            <p class="text-xs text-slate-500">Vendedor</p>
                            <p class="font-bold">{{ $card->seller_name }}</p>
                            <p class="text-xs uppercase tracking-wide text-slate-500">{{ $card->seller_type === 'loja' ? 'Loja' : 'Player' }}</p>
                        </div>
                        <p class="text-2xl font-black text-arena-gold">R$ {{ number_format($card->price, 2, ',', '.') }}</p>
                    </div>
                </div>
            </a>
        @empty
            <div class="arena-card p-6 md:col-span-2 lg:col-span-3">
                <p class="font-bold text-white">Nenhuma carta encontrada.</p>
                <p class="mt-2 text-slate-400">Seja o primeiro a cadastrar uma carta para venda neste filtro.</p>
            </div>
        @endforelse
    </div>
</x-layouts.app>
