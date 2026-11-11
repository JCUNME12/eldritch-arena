<x-layouts.app title="Marketplace — Eldritch Arena">
    <div class="flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
        <div>
            <p class="font-bold text-arena-cyan">Cartas e colecionáveis</p>
            <h1 class="arena-section-title">Marketplace</h1>
            <p class="mt-2 max-w-2xl text-slate-400">Encontre cartas para sua coleção. Os anúncios comuns têm negociação direta. Experimente o carrinho e checkout simulado nas lojas de demonstração.</p>
            <a class="arena-btn-secondary mt-3" href="{{ route('showcase.index') }}">Comprar nas lojas demo</a>
            <a class="arena-btn-secondary mt-3" href="{{ route('cart.index') }}">Meu carrinho demo</a>
        </div>
        <a href="{{ route('marketplace.create') }}" class="arena-btn">Vender carta</a>
    </div>

    <form method="GET" class="mt-5 flex flex-wrap gap-3"><label class="flex-1"><span class="sr-only">Buscar carta</span><input name="q" class="arena-input" value="{{ request('q') }}" placeholder="Qual carta falta no seu deck?" maxlength="100"></label><input type="hidden" name="game" value="{{ $selectedGame }}"><label><span class="sr-only">Ordenar</span><select name="sort" class="arena-input">@foreach([''=>'Destaques','recent'=>'Mais recentes','price_asc'=>'Menor preço','price_desc'=>'Maior preço'] as $v=>$label)<option value="{{ $v }}" @selected(request('sort','')===$v)>{{ $label }}</option>@endforeach</select></label><label class="self-center text-sm"><input type="checkbox" name="mine" value="1" @checked(request('mine'))> Meus anúncios</label><button class="arena-btn-secondary">Buscar</button></form>
    <div class="mt-5 flex flex-wrap gap-2">
        <a href="{{ route('marketplace') }}" class="{{ !$selectedGame ? 'arena-btn' : 'arena-btn-secondary' }}">Todos</a>
        @foreach($games as $game)
            <a href="{{ route('marketplace', ['game' => $game]) }}" class="{{ $selectedGame === $game ? 'arena-btn' : 'arena-btn-secondary' }}">{{ $game }}</a>
        @endforeach
    </div>

    @if($demoItems->total())
    <section class="mt-8" aria-label="Cartas para comprar na demonstração">
        <p class="eyebrow">COMPRA DEMONSTRATIVA</p>
        <h2 class="text-2xl font-bold">Escolha sua carta</h2>
        <p class="text-slate-400 mt-2">{{ $demoItems->total() }} ofertas com estoque. Clique em Comprar, confira o carrinho e finalize com Pix, cartão ou boleto simulado. Preços fictícios, sem cobrança ou entrega.</p>
        <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3 mt-5">
        @foreach($demoItems as $item)
            <article class="arena-card p-5 flex flex-col gap-3">
                <p class="eyebrow">{{ $item->game }} · {{ $item->store->name }}</p>
                <h3 class="text-xl font-bold">{{ $item->name }}</h3>
                <p class="text-sm text-slate-400">{{ $item->condition }} · {{ $item->quantity }} disponíveis</p>
                <p class="text-3xl font-bold text-arena-gold">R$ {{ number_format($item->price,2,',','.') }}</p>
                <form method="POST" action="{{ route('cart.update',$item) }}">@csrf
                    <input type="hidden" name="quantity" value="1"><input type="hidden" name="add" value="1">
                    <button class="arena-btn w-full" aria-label="Comprar {{ $item->name }} na {{ $item->store->name }}">Comprar</button>
                </form>
                <span class="text-xs text-slate-400">Adiciona ao carrinho · demonstração</span>
            </article>
        @endforeach
        </div>
        <div class="mt-5">{{ $demoItems->links() }}</div>
    </section>
    @endif
    <h2 class="text-2xl font-bold mt-8">Anúncios com negociação direta</h2>
    <div class="mt-6 grid gap-4 md:grid-cols-2 lg:grid-cols-3">
        @forelse($cards as $card)
            <a href="{{ route('marketplace.show', $card) }}" class="arena-card group overflow-hidden transition hover:-translate-y-1 hover:border-arena-cyan/50">
                <div class="grid h-40 place-items-center bg-gradient-to-br from-arena-purple/30 via-black/20 to-arena-cyan/20">
                    @if($card->image_url)
                        <img src="{{ $card->image_url }}" alt="{{ $card->name }}" class="h-full w-full object-cover">
                    @else
                        <span class="font-display text-6xl text-arena-gold" aria-hidden="true">◇</span>
                    @endif
                </div>
                <div class="p-5">
                    <div class="flex items-center justify-between gap-2">
                        <p class="text-sm font-bold text-arena-cyan">{{ $card->game }}</p>
                        @if($card->highlighted)
                            <span class="rounded-full border border-arena-gold/40 bg-arena-gold/10 px-2 py-1 text-xs font-bold text-arena-gold">Plus</span>
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
<div class="mt-6">{{ $cards->links() }}</div></x-layouts.app>
