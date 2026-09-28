<x-layouts.app title="Torneios — Eldritch Arena">
<div class="flex flex-wrap justify-between gap-4 items-end">
<div>
<p class="eyebrow">ENCONTRE SUA PRÓXIMA MESA</p>
<h1 class="arena-section-title">Torneios e encontros</h1>
</div>
<a class="arena-btn" href="{{ route('tournaments.create') }}">Organizar torneio</a>
</div>
<form class="my-6 flex flex-wrap gap-3" method="GET">
<label class="flex-1 min-w-48">
<span class="sr-only">Buscar torneio</span>
<input class="arena-input" name="q" value="{{ request('q') }}" placeholder="Nome do torneio" maxlength="100">
</label>
<label>
<span class="sr-only">Jogo</span>
<select name="game" class="arena-input">
<option value="">Todos os jogos</option>
@foreach(config('cardgames') as $g=>$formats)<option @selected(request('game')===$g)>{{ $g }}</option>
@endforeach</select>
</label>
<label>
<span class="sr-only">Formato</span>
<select class="arena-input" name="format">
<option value="">Todos os formatos</option>
@foreach(collect(config('cardgames'))->flatten()->unique() as $f)<option @selected(request('format')===$f)>{{ $f }}</option>
@endforeach</select>
</label>
<label>
<span class="sr-only">Período</span>
<select class="arena-input" name="scope">
@foreach(['upcoming'=>'Próximos eventos','mine'=>'Meus eventos','all'=>'Todos, incluindo encerrados'] as $v=>$label)<option value="{{ $v }}" @selected(request('scope','upcoming')===$v)>{{ $label }}</option>
@endforeach</select>
</label>
<button class="arena-btn-secondary">Filtrar</button>
</form>
<div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
@forelse($tournaments as $tournament)<a class="arena-card p-6 hover:border-arena-gold/40 transition" href="{{ route('tournaments.show',$tournament) }}">
<p class="eyebrow">{{ $tournament->game }} · {{ $tournament->format ?: 'Formato não informado' }}</p>
<h2 class="text-2xl font-bold">{{ $tournament->title }}</h2>
<p class="text-slate-400 mt-4">{{ $tournament->starts_at->copy()->timezone('America/Sao_Paulo')->format('d/m/Y · H:i') }} (Brasília)</p>
<p class="text-slate-400 text-sm mt-2">{{ $tournament->location }}</p>
<div class="flex justify-between gap-3 border-t border-white/10 mt-5 pt-4">
<span class="text-arena-gold">{{ $tournament->cancelled_at ? 'Cancelado' : ($tournament->starts_at->isPast() ? 'Encerrado' : max(0,$tournament->slots-$tournament->registrations_count).' vagas livres') }}</span>
<span>{{ $tournament->entry_fee > 0 ? 'R$ '.number_format($tournament->entry_fee,2,',','.') : 'Gratuito' }}</span>
</div>
</a>
@empty<div class="empty-state md:col-span-2 lg:col-span-3">
<h2 class="text-xl font-bold text-white">Nenhum torneio neste filtro</h2>
<p class="mt-2">Tente outro período ou organize o próximo encontro.</p>
</div>
@endforelse</div>
<div class="mt-6">{{ $tournaments->links() }}</div>
</x-layouts.app>
