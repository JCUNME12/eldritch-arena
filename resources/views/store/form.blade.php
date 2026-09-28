<x-layouts.app title="Produto da loja — Eldritch Arena">
<a class="arena-btn-secondary" href="{{ route('store.index') }}">Voltar ao estoque</a>
<section class="arena-card p-6 mt-5 max-w-4xl"><h1 class="text-3xl font-bold mb-6">{{ $item->exists ? 'Editar produto' : 'Cadastrar produto' }}</h1>
<form method="POST" action="{{ $item->exists ? route('store.update',$item) : route('store.save') }}">@csrf @if($item->exists) @method('PUT') @endif
<div class="grid gap-4 md:grid-cols-2">
@foreach(['sku'=>'SKU / código interno','name'=>'Nome da carta','edition'=>'Edição'] as $field=>$label)<label class="arena-label">{{ $label }}<input class="arena-input mt-2" name="{{ $field }}" value="{{ old($field,$item->$field) }}" maxlength="{{ $field==='sku'?60:120 }}" @required($field!=='edition')></label>@endforeach
@foreach(['game'=>['Jogo',['Magic','Pokémon','Yu-Gi-Oh']],'rarity'=>['Raridade',['Comum','Incomum','Rara','Mítica','Ultra Rara','Secreta','Promo']],'condition'=>['Condição',['Novo','Excelente','Bom','Usado','Danificado']]] as $field=>[$label,$options])<label class="arena-label">{{ $label }}<select class="arena-input mt-2" name="{{ $field }}">@foreach($options as $option)<option @selected(old($field,$item->$field)===$option)>{{ $option }}</option>@endforeach</select></label>@endforeach
@foreach(['cost'=>'Custo unitário (R$) · privado','price'=>'Preço de venda (R$)'] as $field=>$label)<label class="arena-label">{{ $label }}<input class="arena-input mt-2" type="number" min="0" max="999999.99" step="0.01" name="{{ $field }}" value="{{ old($field,$item->$field ?? 0) }}" required></label>@endforeach
<label class="arena-label">Alerta a partir de (unidades)<input class="arena-input mt-2" type="number" name="minimum_quantity" min="0" max="999999" value="{{ old('minimum_quantity',$item->minimum_quantity ?? 2) }}" required></label>
@unless($item->exists)<label class="arena-label">Quantidade inicial<input class="arena-input mt-2" type="number" name="quantity" min="0" max="999999" value="{{ old('quantity',0) }}" required></label>@endunless
<label class="arena-label md:col-span-2">URL da imagem (opcional)<input class="arena-input mt-2" type="url" name="image_url" maxlength="500" value="{{ old('image_url',$item->image_url) }}"></label>
<label class="arena-label md:col-span-2">Descrição pública<textarea class="arena-input mt-2" name="description" maxlength="1000" rows="4">{{ old('description',$item->description) }}</textarea></label>
</div><p class="text-sm text-slate-400 my-5">Use um SKU diferente para cada combinação de edição e condição. O produto começa privado; publique pela página do estoque. Para corrigir quantidades, registre uma entrada ou saída com motivo.</p><button class="arena-btn">Salvar produto</button>
</form></section>
</x-layouts.app>
