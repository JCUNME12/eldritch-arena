<x-layouts.app title="Administração — Eldritch Arena">
    <div class="flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
        <div>
            <p class="font-bold text-arena-gold">Central de operação</p>
            <h1 class="arena-section-title">Administração</h1>
            <p class="mt-2 max-w-3xl text-slate-400">Gerencie acessos, torneios, anúncios e publicações da comunidade.</p>
        </div>
        <span class="w-fit rounded-full border border-arena-gold/30 bg-arena-gold/10 px-4 py-2 text-sm font-black text-arena-gold">Acesso administrativo</span>
    </div>

    <section class="mt-6 grid gap-4 sm:grid-cols-2 xl:grid-cols-5">
        @foreach([
            ['Usuários', $stats['users'], 'text-white'],
            ['Organizadores', $stats['organizers'], 'text-arena-cyan'],
            ['Torneios', $stats['tournaments'], 'text-arena-purple'],
            ['Anúncios', $stats['listings'], 'text-arena-gold'],
            ['Tópicos', $stats['topics'], 'text-emerald-300'],
        ] as [$label, $value, $color])
            <div class="arena-card p-5">
                <p class="text-sm font-bold text-slate-400">{{ $label }}</p>
                <p class="mt-2 text-4xl font-black {{ $color }}">{{ $value }}</p>
            </div>
        @endforeach
    </section>

    <section class="arena-card mt-6 overflow-hidden">
        <div class="border-b border-white/10 p-6">
            <h2 class="text-2xl font-black text-white">Usuários e permissões</h2>
            <p class="mt-1 text-sm text-slate-400">Defina o perfil, o acesso administrativo e os benefícios de destaque de cada conta.</p>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-white/10 text-left text-sm">
                <thead class="bg-white/[0.03] text-xs uppercase tracking-wide text-slate-500">
                    <tr><th class="px-6 py-4">Usuário</th><th class="px-6 py-4">Perfil e permissões</th><th class="px-6 py-4 text-right">Ação</th></tr>
                </thead>
                <tbody class="divide-y divide-white/10">
                    @foreach($users as $user)
                        <tr>
                            <td class="px-6 py-4"><strong class="block text-white">{{ $user->name }}</strong><span class="text-slate-500">{{ $user->email }}</span></td>
                            <td colspan="2" class="p-0">
                                <form method="POST" action="{{ route('admin.users.update', $user) }}" class="grid items-center gap-4 px-6 py-4 md:grid-cols-[180px_1fr_auto]">
                                    @csrf @method('PATCH')
                                    <select name="type" class="arena-input py-2">
                                        <option value="player" @selected($user->isPlayer())>Jogador</option>
                                        <option value="organizer" @selected($user->isOrganizer())>Loja/Organizador</option>
                                    </select>
                                    <div class="flex flex-wrap gap-5 text-slate-300">
                                        @if(auth()->id() === $user->id)
                                            <input type="hidden" name="is_admin" value="1">
                                            <span class="font-bold text-arena-gold">✓ Administrador atual</span>
                                        @else
                                            <input type="hidden" name="is_admin" value="0">
                                            <label class="flex items-center gap-2"><input type="checkbox" name="is_admin" value="1" @checked($user->isAdmin()) class="rounded border-white/20 bg-black/30 text-arena-purple"> Administrador</label>
                                        @endif
                                        <input type="hidden" name="premium_active" value="0">
                                        <label class="flex items-center gap-2"><input type="checkbox" name="premium_active" value="1" @checked($user->isPremium()) class="rounded border-white/20 bg-black/30 text-arena-gold"> Destaque ativo</label>
                                    </div>
                                    <button class="arena-btn-secondary px-4 py-2">Salvar</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </section>

    <section class="arena-card mt-6 overflow-hidden">
        <div class="border-b border-white/10 p-6"><h2 class="text-2xl font-black text-white">Torneios recentes</h2></div>
        <div class="divide-y divide-white/10">
            @forelse($tournaments as $tournament)
                <form method="POST" action="{{ route('admin.tournaments.update', $tournament) }}" class="grid items-center gap-4 p-5 lg:grid-cols-[1fr_180px_160px_auto]">
                    @csrf @method('PATCH')
                    <div><a href="{{ route('tournaments.show', $tournament) }}" class="font-black text-white hover:text-arena-cyan">{{ $tournament->title }}</a><p class="text-sm text-slate-500">{{ $tournament->organizer?->name }} • {{ $tournament->starts_at->format('d/m/Y H:i') }} • {{ $tournament->registrations_count }} inscritos</p></div>
                    <select name="status" class="arena-input py-2"><option value="published" @selected($tournament->status === 'published')>Publicado</option><option value="cancelled" @selected($tournament->status === 'cancelled')>Cancelado</option><option value="finished" @selected($tournament->status === 'finished')>Finalizado</option></select>
                    <div><input type="hidden" name="highlighted" value="0"><label class="flex items-center gap-2 text-sm text-slate-300"><input type="checkbox" name="highlighted" value="1" @checked($tournament->highlighted) class="rounded border-white/20 bg-black/30 text-arena-gold"> Em destaque</label></div>
                    <button class="arena-btn-secondary px-4 py-2">Atualizar</button>
                </form>
            @empty
                <p class="p-6 text-slate-400">Nenhum torneio cadastrado.</p>
            @endforelse
        </div>
    </section>

    <div class="mt-6 grid gap-6 xl:grid-cols-2">
        <section class="arena-card overflow-hidden">
            <div class="border-b border-white/10 p-6"><h2 class="text-2xl font-black text-white">Anúncios recentes</h2></div>
            <div class="divide-y divide-white/10">
                @forelse($listings as $card)
                    <div class="flex items-center justify-between gap-4 p-5">
                        <div><a href="{{ route('marketplace.show', $card) }}" class="font-black text-white hover:text-arena-cyan">{{ $card->name }}</a><p class="text-sm text-slate-500">{{ $card->user?->name ?? $card->seller_name }} • R$ {{ number_format($card->price, 2, ',', '.') }}</p></div>
                        <form method="POST" action="{{ route('admin.listings.destroy', $card) }}" onsubmit="return confirm('Remover este anúncio do marketplace?')">@csrf @method('DELETE')<button class="rounded-xl border border-red-400/30 px-3 py-2 text-xs font-bold text-red-300 hover:bg-red-500/10">Remover</button></form>
                    </div>
                @empty
                    <p class="p-6 text-slate-400">Nenhum anúncio cadastrado.</p>
                @endforelse
            </div>
        </section>

        <section class="arena-card overflow-hidden">
            <div class="border-b border-white/10 p-6"><h2 class="text-2xl font-black text-white">Moderação da comunidade</h2></div>
            <div class="divide-y divide-white/10">
                @forelse($topics as $topic)
                    <div class="grid items-center gap-3 p-5 sm:grid-cols-[1fr_auto_auto]">
                        <div><a href="{{ route('community.show', $topic) }}" class="font-black text-white hover:text-arena-cyan">{{ $topic->title }}</a><p class="text-sm text-slate-500">{{ $topic->user?->name }} • {{ $topic->comments_count }} comentários</p></div>
                        <form method="POST" action="{{ route('admin.topics.update', $topic) }}">@csrf @method('PATCH')<input type="hidden" name="is_pinned" value="{{ $topic->is_pinned ? 0 : 1 }}"><button class="arena-btn-secondary px-3 py-2 text-xs">{{ $topic->is_pinned ? 'Desafixar' : 'Fixar' }}</button></form>
                        <form method="POST" action="{{ route('admin.topics.destroy', $topic) }}" onsubmit="return confirm('Remover este tópico e todos os comentários?')">@csrf @method('DELETE')<button class="rounded-xl border border-red-400/30 px-3 py-2 text-xs font-bold text-red-300 hover:bg-red-500/10">Remover</button></form>
                    </div>
                @empty
                    <p class="p-6 text-slate-400">Nenhum tópico publicado.</p>
                @endforelse
            </div>
        </section>
    </div>
</x-layouts.app>
