<x-layouts.app title="Mostra — Eldritch Arena">
@include('showcase.banner')
<header class="max-w-3xl mb-8"><p class="eyebrow">DA CARTA À VENDA</p><h1 class="arena-section-title">Duas lojas. Uma experiência completa.</h1><p class="text-slate-400 text-lg mt-4 leading-8">Explore a operação gratuita e a gestão Plus. Acompanhe um pagamento do checkout ao estoque, sem sair da Arena.</p></header>
<div class="grid md:grid-cols-2 gap-6">
@forelse($stores as $store)
<article class="arena-card p-6 sm:p-8 border-t-4 {{ $store->user->isPremium() ? 'border-t-amber-300' : 'border-t-cyan-300' }}">
<p class="eyebrow">{{ $store->user->isPremium() ? 'ARENA PLUS' : 'ARENA GRATUITA' }}</p><h2 class="text-3xl font-bold">{{ $store->name }}</h2>
<p class="text-slate-400 mt-3">{{ $store->user->isPremium() ? 'Visão financeira, relatórios e taxa ilustrativa reduzida.' : 'Catálogo, estoque e acompanhamento de pedidos para começar.' }}</p>
<p class="text-3xl font-bold mt-6">{{ $store->user->isPremium() ? 'R$ 49,90' : 'R$ 0' }}<span class="text-sm font-normal text-slate-400"> / mês · proposta demonstrativa</span></p>
<a href="{{ route('showcase.store',$store) }}" class="arena-btn mt-6 w-full">Abrir painel {{ $store->user->isPremium() ? 'Plus' : 'gratuito' }}</a>
</article>
@empty<div class="arena-card p-8 md:col-span-2"><h2 class="font-bold text-xl">Demonstração ainda não preparada para sua conta</h2><p class="text-slate-400 mt-3">As lojas da mostra são disponibilizadas ao apresentador. Entre com a conta usada na preparação ou solicite acesso ao administrador.</p></div>@endforelse
</div>
<section class="arena-card p-6 mt-8"><h2 class="text-xl font-bold mb-5">O que muda entre os planos</h2><div class="overflow-x-auto"><table class="w-full text-sm text-left"><thead><tr class="border-b border-white/10"><th class="p-3">Recurso</th><th class="p-3">Gratuito</th><th class="p-3 text-arena-gold">Plus</th></tr></thead><tbody>
@foreach([['Catálogo, saldo e alertas de estoque','Incluído','Incluído'],['Pedidos e histórico do pagamento simulado','Incluído','Incluído'],['Análise de taxas e margem estimada','—','Incluído'],['Exportação CSV de vendas simuladas','—','Incluído'],['Taxa ilustrativa por venda','5%','2%'],['Assinatura ilustrativa','R$ 0','R$ 49,90 / mês']] as $row)<tr class="border-b border-white/5">@foreach($row as $cell)<td class="p-3">{{ $cell }}</td>@endforeach</tr>@endforeach
</tbody></table></div><p class="text-xs text-slate-400 mt-4">Proposta de produto para a mostra. Não é oferta comercial nem tabela de um provedor. Integração bancária, recorrência automática, frete e emissão fiscal não estão implementados.</p></section>
<section class="grid sm:grid-cols-3 gap-4 mt-6">@foreach(['01 · Comprar'=>'Abra uma loja, escolha uma carta e selecione Pix, cartão ou boleto.','02 · Demonstrar'=>'Aprove ou recuse o pagamento. Mostre o saldo antes e depois; depois teste um estorno.','03 · Evoluir'=>'Na loja gratuita, simule a assinatura Plus e veja os recursos liberados após a aprovação.'] as $title=>$copy)<div class="arena-card p-5"><h2 class="font-bold">{{ $title }}</h2><p class="text-sm text-slate-400 mt-3">{{ $copy }}</p></div>@endforeach</section>
</x-layouts.app>
