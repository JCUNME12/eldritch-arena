<x-layouts.app title="Editar anúncio — Eldritch Arena">
    <div class="mx-auto max-w-4xl">
        <section class="arena-card p-6 md:p-8">
            <p class="font-bold text-arena-cyan">Marketplace</p>
            <h1 class="arena-section-title">Editar anúncio</h1>
            <p class="mt-2 text-slate-400">Mantenha preço, condição e descrição atualizados para os compradores.</p>

            <form method="POST" action="{{ route('marketplace.update', $card) }}" class="mt-6 grid gap-4">
                @csrf @method('PUT')
                <div><label for="name" class="arena-label">Nome da carta</label><input id="name" name="name" value="{{ old('name', $card->name) }}" required class="arena-input mt-2">@error('name')<p class="mt-1 text-sm text-red-300">{{ $message }}</p>@enderror</div>
                <div class="grid gap-4 md:grid-cols-2">
                    <div><label for="game" class="arena-label">Jogo</label><select id="game" name="game" required class="arena-input mt-2">@foreach($games as $game)<option value="{{ $game }}" @selected(old('game', $card->game) === $game)>{{ $game }}</option>@endforeach</select></div>
                    <div><label for="edition" class="arena-label">Edição ou coleção</label><input id="edition" name="edition" value="{{ old('edition', $card->edition) }}" class="arena-input mt-2"></div>
                </div>
                <div class="grid gap-4 md:grid-cols-3">
                    <div><label for="rarity" class="arena-label">Raridade</label><select id="rarity" name="rarity" required class="arena-input mt-2">@foreach($rarities as $rarity)<option value="{{ $rarity }}" @selected(old('rarity', $card->rarity) === $rarity)>{{ $rarity }}</option>@endforeach</select></div>
                    <div><label for="condition" class="arena-label">Estado</label><select id="condition" name="condition" required class="arena-input mt-2">@foreach($conditions as $condition)<option value="{{ $condition }}" @selected(old('condition', $card->condition) === $condition)>{{ $condition }}</option>@endforeach</select></div>
                    <div><label for="price" class="arena-label">Preço</label><input id="price" name="price" value="{{ old('price', $card->price) }}" required type="number" step="0.01" min="0" class="arena-input mt-2"></div>
                </div>
                <div><label for="image_url" class="arena-label">URL da imagem</label><input id="image_url" name="image_url" value="{{ old('image_url', $card->image_url) }}" type="url" class="arena-input mt-2">@error('image_url')<p class="mt-1 text-sm text-red-300">{{ $message }}</p>@enderror</div>
                <div><label for="description" class="arena-label">Descrição</label><textarea id="description" name="description" rows="5" class="arena-input mt-2">{{ old('description', $card->description) }}</textarea></div>
                <div class="flex flex-col gap-3 sm:flex-row"><button class="arena-btn">Salvar alterações</button><a href="{{ route('marketplace.show', $card) }}" class="arena-btn-secondary text-center">Cancelar</a></div>
            </form>
        </section>
    </div>
</x-layouts.app>
