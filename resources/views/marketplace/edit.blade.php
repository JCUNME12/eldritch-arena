<x-layouts.app title="Editar anúncio — Eldritch Arena">
<section class="arena-card p-6 max-w-3xl mx-auto">
<p class="eyebrow">MEUS CLASSIFICADOS</p>
<h1 class="arena-section-title">Editar anúncio</h1>
<form method="POST" action="{{ route('marketplace.update',$card) }}" class="grid gap-5 mt-6">@csrf @method('PATCH')<label class="arena-label">Nome da carta<input class="arena-input mt-2" name="name" value="{{ old('name',$card->name) }}" required maxlength="120">
</label>
<label class="arena-label">Edição<input name="edition" class="arena-input mt-2" value="{{ old('edition',$card->edition) }}" maxlength="120">
</label>
<div class="grid sm:grid-cols-2 gap-4">
<label class="arena-label">Estado<select name="condition" class="arena-input mt-2">
@foreach(['Novo','Excelente','Bom','Usado','Danificado'] as $v)<option @selected(old('condition',$card->condition)===$v)>{{ $v }}</option>
@endforeach</select>
</label>
<label class="arena-label">Raridade<select name="rarity" class="arena-input mt-2">
@foreach(['Comum','Incomum','Rara','Mítica','Ultra Rara','Secreta','Promo'] as $v)<option @selected(old('rarity',$card->rarity)===$v)>{{ $v }}</option>
@endforeach</select>
</label>
</div>
<label class="arena-label">Preço (R$)<input type="number" step="0.01" min="0" max="999999.99" name="price" value="{{ old('price',$card->price) }}" required class="arena-input mt-2">
</label>
<label class="arena-label">URL da imagem<input type="url" name="image_url" value="{{ old('image_url',$card->image_url) }}" maxlength="500" class="arena-input mt-2">
</label>
<label class="arena-label">Descrição<textarea name="description" rows="4" maxlength="1000" class="arena-input mt-2">{{ old('description',$card->description) }}</textarea>
</label>
<div class="flex gap-3">
<button class="arena-btn">Salvar anúncio</button>
<a class="arena-btn-secondary" href="{{ route('marketplace.show',$card) }}">Voltar</a>
</div>
</form>
</section>
</x-layouts.app>
