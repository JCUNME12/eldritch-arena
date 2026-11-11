<x-layouts.app title="Pedido demonstrativo">
@include('showcase.banner')
<h1 class="arena-section-title">Pedido DEMO #{{ $order->id }}</h1>
<p class="text-xl my-4">{{ $order->payments->first()->statusLabel() }} · {{ $order->payments->first()->methodLabel() }}</p>
<section class="arena-card p-6 space-y-4">
@foreach($order->payments as $payment)<div class="flex justify-between gap-4 border-b border-white/10 pb-4"><div><p class="text-sm text-slate-400">{{ $payment->store->name }}</p><p>{{ $payment->quantity }} × {{ $payment->description }}</p></div><strong>R$ {{ number_format($payment->amount_cents/100,2,',','.') }}</strong></div>@endforeach
<p class="text-2xl font-bold">Total: R$ {{ number_format($order->payments->sum('amount_cents')/100,2,',','.') }}</p>
<p class="text-slate-400">{{ ['pending'=>'Pedido recebido. Escolha abaixo um resultado para demonstrar o pagamento. Não há QR Code ou boleto pagável.', 'paid'=>'Compra simulada aprovada. O estoque de todas as lojas foi atualizado.', 'refunded'=>'Estorno integral simulado. Todas as quantidades retornaram ao estoque.', 'declined'=>'Pagamento recusado. O estoque não foi alterado.', 'cancelled'=>'Pedido cancelado. O estoque não foi alterado.'][$order->status] }}</p>
</section>
<div class="flex flex-wrap gap-4 my-6">@foreach(($order->status === 'pending' ? ['paid'=>'Simular aprovação','declined'=>'Simular recusa','cancelled'=>'Cancelar pedido'] : ($order->status === 'paid' ? ['refunded'=>'Simular estorno integral'] : [])) as $state=>$label)
<form method="POST" action="{{ route('cart.transition',$order) }}">@csrf<input type="hidden" name="status" value="{{ $state }}"><button class="arena-btn-secondary">{{ $label }}</button></form>@endforeach</div>
<h2 class="text-xl font-bold mb-3">Histórico</h2><ul class="space-y-2 text-slate-300">@foreach($order->payments->first()->events as $event)<li>{{ ['pending'=>'Pedido criado','paid'=>'Aprovação simulada','declined'=>'Recusa simulada','cancelled'=>'Cancelamento','refunded'=>'Estorno integral'][$event['status']] }} · {{ \Carbon\Carbon::parse($event['at'])->format('d/m/Y H:i:s') }}</li>@endforeach</ul>
<a class="arena-btn mt-6" href="{{ route('cart.index') }}">Voltar ao carrinho</a>
</x-layouts.app>
