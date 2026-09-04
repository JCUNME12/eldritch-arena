<x-layouts.app title="{{ $card->name }} — Marketplace">
    <div class="mb-5">
        <a href="{{ route('marketplace') }}" class="arena-btn-secondary">Voltar ao marketplace</a>
    </div>

    <div class="grid gap-6 lg:grid-cols-[.9fr_1.1fr]">
        <section class="arena-card overflow-hidden">
            <div class="grid min-h-[360px] place-items-center bg-gradient-to-br from-arena-purple/30 via-black/20 to-arena-cyan/20">
                @if($card->image_url)
                    <img src="{{ $card->image_url }}" alt="{{ $card->name }}" class="h-full w-full object-cover">
                @else
                    <span class="text-8xl">🃏</span>
                @endif
            </div>
        </section>

        <section class="arena-card p-6">
            <div class="flex flex-wrap items-center gap-2">
                <span class="rounded-full border border-arena-cyan/40 bg-arena-cyan/10 px-3 py-1 text-sm font-bold text-arena-cyan">{{ $card->game }}</span>
                @if($card->highlighted)
                    <span class="rounded-full border border-arena-gold/40 bg-arena-gold/10 px-3 py-1 text-sm font-bold text-arena-gold">Anúncio em destaque</span>
                @endif
            </div>

            <h1 class="arena-section-title mt-4">{{ $card->name }}</h1>
            <p class="mt-3 text-slate-400">{{ $card->rarity }} • {{ $card->condition }}@if($card->edition) • {{ $card->edition }}@endif</p>

            <div class="mt-6 rounded-3xl border border-white/10 bg-white/5 p-5">
                <p class="text-sm font-bold uppercase tracking-wide text-slate-500">Preço anunciado</p>
                <p class="mt-2 text-4xl font-black text-arena-gold">R$ {{ number_format($card->price, 2, ',', '.') }}</p>
            </div>

            <div class="mt-6 grid gap-4 md:grid-cols-2">
                <div class="rounded-3xl border border-white/10 bg-black/20 p-5">
                    <p class="text-xs font-bold uppercase tracking-wide text-slate-500">Vendedor</p>
                    <p class="mt-2 text-xl font-black text-white">{{ $card->seller_name }}</p>
                    <p class="mt-1 text-sm text-slate-400">{{ $card->seller_type === 'loja' ? 'Lojista / organizador' : 'Player da comunidade' }}</p>
                </div>
                <div class="rounded-3xl border border-white/10 bg-black/20 p-5">
                    <p class="text-xs font-bold uppercase tracking-wide text-slate-500">Contato</p>
                    <p class="mt-2 break-all text-sm font-bold text-arena-cyan">{{ $card->contact_email ?? 'Contato pelo marketplace' }}</p>
                    <p class="mt-1 text-sm text-slate-400">Combine pagamento, entrega e verificação da carta diretamente com o vendedor.</p>
                </div>
            </div>

            <div class="mt-6">
                <h2 class="text-2xl font-black text-white">Descrição</h2>
                <p class="mt-3 whitespace-pre-line text-slate-300">{{ $card->description ?: 'O vendedor ainda não adicionou uma descrição detalhada para esta carta.' }}</p>
            </div>

            <div class="mt-6 flex flex-col gap-3 sm:flex-row">
                <a href="mailto:{{ $card->contact_email }}?subject=Tenho interesse na carta {{ urlencode($card->name) }}" class="arena-btn text-center">Tenho interesse</a>
                <a href="{{ route('marketplace.create') }}" class="arena-btn-secondary text-center">Anunciar outra carta</a>
                @if(auth()->user()->isAdmin() || auth()->id() === $card->user_id)
                    <a href="{{ route('marketplace.edit', $card) }}" class="arena-btn-secondary text-center">Editar anúncio</a>
                    <form method="POST" action="{{ route('marketplace.destroy', $card) }}" onsubmit="return confirm('Remover este anúncio do marketplace?')">@csrf @method('DELETE')<button class="arena-btn-secondary w-full border-red-400/40 text-red-200 hover:bg-red-500/10">Excluir</button></form>
                @endif
            </div>
        </section>
    </div>
</x-layouts.app>
