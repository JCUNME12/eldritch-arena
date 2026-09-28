<x-layouts.app title="{{ $tournament->title }} — Eldritch Arena">
<a class="text-sm text-slate-400" href="{{ route('tournaments.index') }}">← Todos os torneios</a>
<section class="arena-card mt-5 p-6 md:p-10">
<p class="eyebrow">{{ $tournament->game }} · {{ $tournament->format ?: 'Formato a confirmar com o organizador' }}</p>
<h1 class="arena-section-title">{{ $tournament->title }}</h1>
<p class="mt-3 text-slate-400">Organizado por {{ $tournament->organizer?->name }}</p>
<p class="mt-6 whitespace-pre-line max-w-3xl text-slate-300">{{ $tournament->description }}</p>
<div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-4 border-y border-white/10 py-6 my-6">
<div>
<p class="eyebrow">QUANDO</p>{{ $tournament->starts_at->copy()->timezone('America/Sao_Paulo')->format('d/m/Y H:i') }}<p class="text-xs text-slate-400">Horário de Brasília</p>
</div>
<div>
<p class="eyebrow">ONDE</p>{{ $tournament->location }}</div>
<div>
<p class="eyebrow">INSCRIÇÃO</p>{{ $tournament->entry_fee > 0 ? 'R$ '.number_format($tournament->entry_fee,2,',','.') : 'Gratuita' }}</div>
<div>
<p class="eyebrow">PARTICIPANTES</p>{{ $tournament->registrations_count }} / {{ $tournament->slots }}</div>
</div>
<p class="text-slate-400">Premiação</p>
<p class="text-xl font-bold text-arena-gold mt-1">{{ $tournament->prize }}</p>
@php($closed = $tournament->cancelled_at || $tournament->starts_at->isPast())
<div class="mt-6">@if($tournament->cancelled_at)<p class="text-red-300 font-bold">Torneio cancelado pelo organizador.</p>@elseif($tournament->starts_at->isPast())<p class="text-slate-400">Inscrições encerradas: o horário de início já passou.</p>@endif
@if(auth()->id()===$tournament->organizer_id)<div class="flex flex-wrap gap-3 mt-4">@unless($closed)<a class="arena-btn" href="{{ route('tournaments.edit',$tournament) }}">Editar torneio</a>
<form method="POST" action="{{ route('tournaments.cancel',$tournament) }}" onsubmit="return confirm('Cancelar este torneio para todos os participantes?')">@csrf @method('PATCH')<button class="arena-btn-secondary">Cancelar torneio</button>
</form>@endunless</div>
<h2 class="font-bold text-xl mt-8">Lista de inscritos</h2>
<ul class="mt-3 divide-y divide-white/10">
@forelse($tournament->registrations as $registration)<li class="py-3">{{ $registration->user?->name }} <span class="text-xs text-slate-400">· {{ $registration->created_at->format('d/m/Y') }}</span>
</li>
@empty<li class="text-slate-400">Nenhuma inscrição ainda.</li>
@endforelse</ul>
@elseif($tournament->isUserRegistered(auth()->user()))<p class="text-arena-cyan mt-3">Sua inscrição está confirmada.</p>@if(!$tournament->starts_at->isPast() || $tournament->cancelled_at)<form class="mt-3" method="POST" action="{{ route('tournaments.unregister',$tournament) }}" onsubmit="return confirm('Cancelar sua inscrição?')">@csrf @method('DELETE')<button class="arena-btn-secondary">Cancelar minha inscrição</button>
</form>@endif
@elseif(!$closed && $tournament->registrations_count < $tournament->slots)<form method="POST" action="{{ route('tournaments.register',$tournament) }}">@csrf<button class="arena-btn">Confirmar minha inscrição</button>
</form>@elseif(!$closed)<p class="text-arena-gold">Todas as vagas foram preenchidas.</p>@endif</div>
<p class="text-xs text-slate-400 mt-6">A inscrição reserva sua vaga. Se houver taxa, combine o pagamento diretamente com o organizador.</p>
</section>
</x-layouts.app>
