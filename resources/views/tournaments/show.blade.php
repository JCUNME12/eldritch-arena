<x-layouts.app title="{{ $tournament->title }} — Eldritch Arena">
    <div class="mb-5 flex flex-wrap items-center justify-between gap-3">
        <a href="{{ route('tournaments.index') }}" class="arena-btn-secondary">← Voltar aos torneios</a>
        @if(auth()->user()->isAdmin() || auth()->id() === $tournament->organizer_id)
            <div class="flex flex-wrap gap-3">
                <a href="{{ route('tournaments.edit', $tournament) }}" class="arena-btn-secondary">Editar torneio</a>
                @if($tournament->status !== \App\Models\Tournament::STATUS_CANCELLED)
                    <form method="POST" action="{{ route('tournaments.cancel', $tournament) }}" onsubmit="return confirm('Cancelar este torneio?')">@csrf @method('PATCH')<button class="arena-btn-secondary border-red-400/40 text-red-200 hover:bg-red-500/10">Cancelar torneio</button></form>
                @endif
            </div>
        @endif
    </div>

    <div class="arena-card overflow-hidden">
        <div class="bg-gradient-to-r from-arena-purple/30 to-arena-cyan/10 p-6 md:p-8">
            <div class="flex flex-wrap items-center gap-3"><p class="font-bold text-arena-cyan">{{ $tournament->game }}</p>@if($tournament->status === 'cancelled')<span class="rounded-full border border-red-400/40 bg-red-500/10 px-3 py-1 text-xs font-black uppercase text-red-200">Cancelado</span>@elseif($tournament->status === 'finished')<span class="rounded-full border border-white/20 bg-white/10 px-3 py-1 text-xs font-black uppercase text-slate-300">Finalizado</span>@endif</div>
            <h1 class="mt-2 font-display text-4xl font-black">{{ $tournament->title }}</h1>
            <p class="mt-3 max-w-3xl text-slate-300">{{ $tournament->description }}</p>
        </div>
        <div class="grid gap-4 p-6 md:grid-cols-4">
            <div><p class="text-sm text-slate-400">Data</p><p class="font-bold">{{ $tournament->starts_at->format('d/m/Y H:i') }}</p></div>
            <div><p class="text-sm text-slate-400">Local</p><p class="font-bold">{{ $tournament->location }}</p></div>
            <div><p class="text-sm text-slate-400">Inscrição</p><p class="font-bold">R$ {{ number_format($tournament->entry_fee, 2, ',', '.') }}</p></div>
            <div><p class="text-sm text-slate-400">Vagas</p><p class="font-bold">{{ $tournament->registrations_count }}/{{ $tournament->slots }}</p></div>
        </div>
        <div class="border-t border-white/10 p-6">
            <p class="text-sm text-slate-400">Premiação</p><p class="mt-1 text-2xl font-black text-arena-gold">{{ $tournament->prize }}</p>
            <div class="mt-5">
                @if($tournament->isUserRegistered(auth()->user()))
                    <div class="flex flex-wrap items-center gap-3"><span class="rounded-2xl border border-emerald-400/30 bg-emerald-500/10 px-4 py-3 text-sm font-bold text-emerald-200">Inscrição confirmada</span><form method="POST" action="{{ route('tournaments.unregister', $tournament) }}" onsubmit="return confirm('Cancelar sua inscrição?')">@csrf @method('DELETE')<button class="arena-btn-secondary border-red-400/40 text-red-200">Cancelar inscrição</button></form></div>
                @elseif($tournament->isOpenForRegistration())
                    <form method="POST" action="{{ route('tournaments.register', $tournament) }}">@csrf<button class="arena-btn">Inscrever-se no torneio</button></form>
                @else
                    <span class="rounded-2xl border border-white/10 bg-white/5 px-4 py-3 text-sm font-bold text-slate-400">Inscrições encerradas</span>
                @endif
            </div>
        </div>
    </div>
</x-layouts.app>
