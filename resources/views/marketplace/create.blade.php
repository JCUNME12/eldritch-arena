<x-layouts.app title="Vender carta — Eldritch Arena">
    <div class="grid gap-6 lg:grid-cols-[1.1fr_.9fr]">
        <section class="arena-card p-6">
            <p class="font-bold text-arena-cyan">Marketplace</p>
            <h1 class="arena-section-title">Vender carta</h1>
            <p class="mt-2 text-slate-400">Cadastre as informações da carta com clareza para facilitar o contato com compradores interessados.</p>

            <form method="POST" action="{{ route('marketplace.store') }}" class="mt-6 grid gap-4">
                @csrf

                <div>
                    <label for="name" class="text-sm font-bold text-slate-200">Nome da carta</label>
                    <input id="name" name="name" value="{{ old('name') }}" required class="mt-2 w-full rounded-2xl border border-white/10 bg-black/30 px-4 py-3 text-white outline-none focus:border-arena-cyan" placeholder="Ex.: Dragão Branco de Olhos Azuis">
                    @error('name')<p class="mt-1 text-sm text-red-300">{{ $message }}</p>@enderror
                </div>

                <div class="grid gap-4 md:grid-cols-2">
                    <div>
                        <label for="game" class="text-sm font-bold text-slate-200">Jogo</label>
                        <select id="game" name="game" required class="mt-2 w-full rounded-2xl border border-white/10 bg-black/30 px-4 py-3 text-white outline-none focus:border-arena-cyan">
                            @foreach($games as $game)
                                <option value="{{ $game }}" @selected(old('game') === $game)>{{ $game }}</option>
                            @endforeach
                        </select>
                        @error('game')<p class="mt-1 text-sm text-red-300">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label for="edition" class="text-sm font-bold text-slate-200">Edição ou coleção</label>
                        <input id="edition" name="edition" value="{{ old('edition') }}" class="mt-2 w-full rounded-2xl border border-white/10 bg-black/30 px-4 py-3 text-white outline-none focus:border-arena-cyan" placeholder="Ex.: Base Set, Commander Masters">
                        @error('edition')<p class="mt-1 text-sm text-red-300">{{ $message }}</p>@enderror
                    </div>
                </div>

                <div class="grid gap-4 md:grid-cols-3">
                    <div>
                        <label for="rarity" class="text-sm font-bold text-slate-200">Raridade</label>
                        <select id="rarity" name="rarity" required class="mt-2 w-full rounded-2xl border border-white/10 bg-black/30 px-4 py-3 text-white outline-none focus:border-arena-cyan">
                            @foreach($rarities as $rarity)
                                <option value="{{ $rarity }}" @selected(old('rarity') === $rarity)>{{ $rarity }}</option>
                            @endforeach
                        </select>
                        @error('rarity')<p class="mt-1 text-sm text-red-300">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label for="condition" class="text-sm font-bold text-slate-200">Estado</label>
                        <select id="condition" name="condition" required class="mt-2 w-full rounded-2xl border border-white/10 bg-black/30 px-4 py-3 text-white outline-none focus:border-arena-cyan">
                            @foreach($conditions as $condition)
                                <option value="{{ $condition }}" @selected(old('condition') === $condition)>{{ $condition }}</option>
                            @endforeach
                        </select>
                        @error('condition')<p class="mt-1 text-sm text-red-300">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label for="price" class="text-sm font-bold text-slate-200">Preço</label>
                        <input id="price" name="price" value="{{ old('price') }}" required type="number" step="0.01" min="0" class="mt-2 w-full rounded-2xl border border-white/10 bg-black/30 px-4 py-3 text-white outline-none focus:border-arena-cyan" placeholder="0,00">
                        @error('price')<p class="mt-1 text-sm text-red-300">{{ $message }}</p>@enderror
                    </div>
                </div>

                <div>
                    <label for="image_url" class="text-sm font-bold text-slate-200">URL da imagem da carta</label>
                    <input id="image_url" name="image_url" value="{{ old('image_url') }}" type="url" class="mt-2 w-full rounded-2xl border border-white/10 bg-black/30 px-4 py-3 text-white outline-none focus:border-arena-cyan" placeholder="https://exemplo.com/carta.jpg">
                    @error('image_url')<p class="mt-1 text-sm text-red-300">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="description" class="text-sm font-bold text-slate-200">Descrição do anúncio</label>
                    <textarea id="description" name="description" rows="5" class="mt-2 w-full rounded-2xl border border-white/10 bg-black/30 px-4 py-3 text-white outline-none focus:border-arena-cyan" placeholder="Informe conservação, idioma, edição, observações e forma de contato.">{{ old('description') }}</textarea>
                    @error('description')<p class="mt-1 text-sm text-red-300">{{ $message }}</p>@enderror
                </div>

                <div class="flex flex-col gap-3 sm:flex-row">
                    <button class="arena-btn">Cadastrar carta</button>
                    <a href="{{ route('marketplace') }}" class="arena-btn-secondary text-center">Voltar ao marketplace</a>
                </div>
            </form>
        </section>

        <aside class="arena-card p-6">
            <h2 class="text-2xl font-black text-white">Boas práticas para anunciar</h2>
            <div class="mt-5 grid gap-3 text-sm text-slate-300">
                <p class="rounded-2xl border border-white/10 bg-white/5 p-4">Informe corretamente a edição, a raridade e o estado de conservação.</p>
                <p class="rounded-2xl border border-white/10 bg-white/5 p-4">Use uma imagem nítida e atual da carta sempre que possível.</p>
                <p class="rounded-2xl border border-white/10 bg-white/5 p-4">Combine pagamento e entrega com segurança antes de concluir a negociação.</p>
            </div>

            @if(auth()->user()->isPremium())
                <div class="mt-5 rounded-3xl border border-arena-gold/30 bg-arena-gold/10 p-5">
                    <p class="font-black text-arena-gold">Conta com destaque ativo</p>
                    <p class="mt-2 text-sm text-slate-300">Seu anúncio receberá destaque editorial no marketplace.</p>
                </div>
            @endif
        </aside>
    </div>
</x-layouts.app>
