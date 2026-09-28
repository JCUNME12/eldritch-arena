<x-layouts.app title="Arena Plus — Eldritch Arena">
<section class="mx-auto max-w-3xl py-10">
<p class="eyebrow">ARENA PLUS · ACESSO ANTECIPADO</p>
<h1 class="arena-section-title">Mais destaque para suas cartas.</h1>
<p class="text-slate-400 text-lg leading-8 mt-5">Experimente o destaque de anúncios da Arena. Durante o acesso antecipado, a ativação é gratuita e não exige cartão.</p>
<div class="arena-card mt-8 p-6">
<h2 class="text-2xl font-bold">{{ auth()->user()->isPremium() ? 'Seu acesso está ativo' : 'Conheça o destaque Plus' }}</h2>
<p class="mt-3 text-slate-400">Novos anúncios recebem o selo Plus e prioridade na listagem padrão. Recursos adicionais e planos pagos ainda não estão disponíveis.</p>@if(auth()->user()->isPremium())<form class="mt-5" method="POST" action="{{ route('premium.cancel') }}">@csrf @method('DELETE')<button class="arena-btn-secondary">Desativar acesso Plus</button>
</form>@else<form method="POST" action="{{ route('premium.subscribe') }}" class="mt-5">@csrf<input type="hidden" name="plan" value="{{ auth()->user()->isOrganizer() ? 'loja_premium' : 'player_premium' }}">
<button class="arena-btn">Ativar acesso gratuito</button>
</form>@endif</div>
</section>
</x-layouts.app>
