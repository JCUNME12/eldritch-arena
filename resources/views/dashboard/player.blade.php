<x-layouts.app title="Meu painel — Eldritch Arena">
<header class="mb-8 flex flex-wrap justify-between gap-4 items-end">
<div>
<p class="eyebrow">SUA ARENA</p>
<h1 class="arena-section-title">Bom te ver, {{ auth()->user()->name }}.</h1>
<p class="mt-3 text-slate-400">Prepare o deck. O próximo encontro está logo ali.</p>
</div>
<form method="POST" action="{{ route('profile.type') }}">@csrf @method('PATCH')<input type="hidden" name="type" value="organizer">
<button class="arena-btn-secondary">Painel de organizador</button>
</form>
</header>
<section class="arena-card p-6 md:p-8 flex flex-wrap justify-between items-center gap-5">
<div>
<p class="eyebrow">DO DUELO AO COMMANDER</p>
<h2 class="text-3xl font-bold">Sua mesa está pronta.</h2>
<p class="text-slate-400 mt-3">Vida, marcadores e dados. Abra no celular e comece a jogar.</p>
</div>
<a class="arena-btn" href="{{ route('life-counter') }}">Abrir mesa ↗</a>
</section>
<section class="mt-10">
<div class="flex justify-between gap-4 items-center">
<h2 class="text-2xl font-bold">Minhas inscrições</h2>
<a class="text-sm text-arena-gold" href="{{ route('tournaments.index',['scope'=>'mine']) }}">Ver todas →</a>
</div>
<div class="mt-4 divide-y divide-white/10">
@forelse($myEvents as $event)<a href="{{ route('tournaments.show',$event) }}" class="py-4 flex justify-between gap-4">
<div>
<h3 class="font-semibold">{{ $event->title }}</h3>
<p class="text-sm text-slate-400">{{ $event->starts_at->copy()->timezone('America/Sao_Paulo')->format('d/m/Y H:i') }} (Brasília) · {{ $event->format ?: $event->game }}</p>
</div>
<span class="text-sm text-arena-gold">{{ $event->cancelled_at ? 'Cancelado' : ($event->starts_at->isPast() ? 'Encerrado' : 'Confirmada') }}</span>
</a>
@empty<p class="text-slate-400 py-6">Você ainda não se inscreveu em nenhum torneio. Explore os próximos encontros abaixo.</p>
@endforelse</div>
</section>
<section class="mt-10">
<div class="flex justify-between items-center gap-4">
<h2 class="text-2xl font-bold">Próximos encontros</h2>
<a class="text-sm text-arena-gold" href="{{ route('tournaments.index') }}">Explorar →</a>
</div>
<div class="grid gap-4 md:grid-cols-3 mt-4">
@forelse($tournaments as $event)<a class="arena-card p-5" href="{{ route('tournaments.show',$event) }}">
<p class="eyebrow">{{ $event->format ?: $event->game }}</p>
<h3 class="text-xl font-bold">{{ $event->title }}</h3>
<p class="text-sm text-slate-400 mt-4">{{ $event->starts_at->copy()->timezone('America/Sao_Paulo')->format('d/m/Y H:i') }} (Brasília)</p>
<p class="text-sm text-arena-cyan mt-2">{{ max(0,$event->slots-$event->registrations_count) }} vagas disponíveis</p>
</a>
@empty<p class="text-slate-400">Nenhum evento futuro publicado.</p>
@endforelse</div>
</section>
<section class="mt-10">
<div class="flex justify-between items-center gap-4">
<h2 class="text-2xl font-bold">Para sua coleção</h2>
<a class="text-sm text-arena-gold" href="{{ route('marketplace') }}">Ver cartas →</a>
</div>
<div class="grid gap-4 md:grid-cols-3 mt-4">
@forelse($cards as $card)<a class="arena-card p-5" href="{{ route('marketplace.show',$card) }}">
<p class="eyebrow">{{ $card->game }}</p>
<h3 class="text-xl font-bold">{{ $card->name }}</h3>
<p class="text-sm text-slate-400 mt-2">{{ $card->rarity }} · {{ $card->condition }}</p>
<p class="text-xl text-arena-gold mt-4">R$ {{ number_format($card->price,2,',','.') }}</p>
</a>
@empty<p class="text-slate-400">Novos anúncios aparecerão aqui.</p>
@endforelse</div>
</section>
</x-layouts.app>
