<x-layouts.app title="Estoque — Eldritch Arena">
<header class="flex flex-wrap justify-between items-center gap-4 mb-6"><div><p class="eyebrow">ÁREA DO LOJISTA</p><h1 class="text-3xl font-bold">{{ $store->name }}</h1><p class="text-slate-400 mt-2">Estoque e disponibilidade dos seus anúncios.</p></div><a class="arena-btn" href="{{ route('store.create') }}">Cadastrar produto</a></header>
<section class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4 mb-6">
@foreach(['Produtos ativos'=>$stats->products,'Unidades disponíveis'=>$stats->units,'Estoque baixo'=>$low,'Custo do estoque'=>'R$ '.number_format($stats->cost_value,2,',','.')] as $label=>$value)
<div class="arena-card p-5"><p class="text-sm text-slate-400">{{ $label }}</p><p class="text-3xl font-bold mt-3">{{ $value }}</p></div>@endforeach
</section>
<form method="GET" class="flex flex-wrap gap-3 mb-5"><label class="flex-1 min-w-0"><span class="sr-only">Buscar produto ou SKU</span><input class="arena-input" name="q" value="{{ request('q') }}" placeholder="Buscar nome ou SKU" maxlength="100"></label><select aria-label="Filtrar estoque" class="arena-input w-auto" name="stock">@foreach([''=>'Produtos ativos','low'=>'Estoque baixo','zero'=>'Sem estoque','archived'=>'Arquivados'] as $key=>$label)<option value="{{ $key }}" @selected(request('stock','')===$key)>{{ $label }}</option>@endforeach</select><button class="arena-btn-secondary">Filtrar</button><a class="arena-btn-secondary" href="{{ route('store.index') }}">Limpar</a></form>
<div class="grid gap-3">
@forelse($items as $item)
<a href="{{ route('store.show',$item) }}" class="arena-card p-5 flex flex-wrap justify-between items-center gap-4"><div><p class="text-xs text-slate-400">{{ $item->sku }} · {{ $item->game }}</p><h2 class="font-bold text-lg mt-1">{{ $item->name }}</h2><p class="text-sm text-slate-400">{{ $item->edition }} · {{ $item->condition }}</p></div><div class="text-right"><p class="font-bold {{ $item->quantity <= $item->minimum_quantity ? 'text-amber-200' : '' }}">{{ $item->quantity }} unidades</p><p>R$ {{ number_format($item->price,2,',','.') }}</p><p class="text-xs text-slate-400">{{ $item->archived ? 'Arquivado' : ($item->published ? ($item->quantity > 0 ? 'Publicado' : 'Indisponível · sem estoque') : 'Anúncio pausado') }}</p></div></a>
@empty<div class="arena-card p-8 text-center"><h2 class="font-bold text-xl">Nenhum produto encontrado</h2><p class="text-slate-400 mt-2">Cadastre seu primeiro produto ou ajuste os filtros.</p></div>@endforelse
</div><div class="mt-5">{{ $items->links() }}</div>
<details class="arena-card p-5 mt-6"><summary class="cursor-pointer font-bold">Dados da loja</summary><div class="mt-5 max-w-2xl">@include('store.profile-form')</div></details>
<p class="text-sm text-slate-400 mt-6">O custo do estoque usa o custo unitário cadastrado atualmente. Não representa lucro nem fluxo de caixa. Vendas e pagamentos são combinados fora da Arena; registre as saídas após confirmar a venda.</p>
</x-layouts.app>
