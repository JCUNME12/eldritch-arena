<x-layouts.app title="Carrinho de demonstração">
@include('showcase.banner')
<h1 class="arena-section-title mb-6">Seu carrinho</h1>
<p class="text-slate-400 mb-6">Produtos das lojas demonstrativas. Sem frete ou entrega real. O estoque é confirmado ao aprovar o pagamento.</p>
@php($total = 0)
<div class="space-y-4">
@forelse($items as $item)
@php($subtotal = (int) round($item->price * 100) * $cart[$item->id])
@php($total += $subtotal)
<article class="arena-card p-6 flex flex-wrap justify-between gap-4 items-center">
<div><p class="eyebrow">{{ $item->store->name }}</p><h2 class="text-xl font-bold">{{ $item->name }}</h2><p class="text-slate-400">R$ {{ number_format($item->price,2,',','.') }} por unidade · {{ $item->quantity }} disponíveis</p></div>
<form method="POST" action="{{ route('cart.update',$item) }}" class="flex gap-3 items-end">@csrf
<label>Quantidade<input class="arena-input w-24" type="number" name="quantity" min="0" max="99" value="{{ $cart[$item->id] }}" required></label><button class="arena-btn-secondary">Atualizar</button></form>
<form method="POST" action="{{ route('cart.update',$item) }}">@csrf<input type="hidden" name="quantity" value="0"><button class="text-rose-300 underline">Remover {{ $item->name }}</button></form>
<strong>R$ {{ number_format($subtotal/100,2,',','.') }}</strong></article>
@empty<div class="arena-card p-8"><h2 class="text-xl font-bold">Seu carrinho está vazio</h2><a class="arena-btn mt-4" href="{{ route('showcase.index') }}">Escolher uma loja demo</a></div>@endforelse
</div>
@if($items->isNotEmpty())
<section class="arena-card p-6 mt-6"><h2 class="text-2xl font-bold">Total · R$ {{ number_format($total/100,2,',','.') }}</h2>
<form method="POST" action="{{ route('cart.checkout') }}" class="mt-5 space-y-5">@csrf<input type="hidden" name="request_id" value="{{ $requestId }}">
<fieldset><legend class="font-bold mb-3">Método de pagamento simulado</legend><div class="flex flex-wrap gap-6">@foreach(['pix'=>'Pix','card'=>'Cartão','boleto'=>'Boleto'] as $value=>$label)<label><input type="radio" name="method" value="{{ $value }}" @checked($loop->first)> {{ $label }}</label>@endforeach</div></fieldset>
<p class="text-sm text-slate-400">Não solicitamos dados bancários. As taxas ilustrativas são descontadas da loja, sem acréscimo ao total. Preços e disponibilidade serão conferidos ao finalizar.</p><button class="arena-btn">Finalizar pedido demonstrativo</button></form></section>
@endif
<a class="arena-btn-secondary mt-6" href="{{ route('showcase.index') }}">Continuar comprando</a>
<section class="mt-8"><h2 class="text-xl font-bold mb-4">Meus últimos pedidos</h2>@forelse($orders as $order)<a class="arena-card block p-4 mb-3" href="{{ route('cart.order',$order) }}">Pedido DEMO #{{ $order->id }} · {{ $order->created_at->format('d/m H:i') }}</a>@empty<p class="text-slate-400">Nenhum pedido finalizado ainda.</p>@endforelse</section>
</x-layouts.app>
