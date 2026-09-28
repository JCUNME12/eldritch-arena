<x-layouts.app title="Organizar torneio — Eldritch Arena">
<section class="mx-auto max-w-3xl arena-card p-6 md:p-8">
<p class="eyebrow">ORGANIZAÇÃO</p>
<h1 class="arena-section-title">{{ $tournament->exists ? 'Editar torneio' : 'Seu próximo torneio' }}</h1>
<p class="text-slate-400 mt-3">Defina o formato e receba inscrições da comunidade.</p>
<form method="POST" action="{{ $tournament->exists ? route('tournaments.update',$tournament) : route('tournaments.store') }}" class="grid gap-5 mt-6" x-data="{games: @js(config('cardgames')), game: @js(old('game',$tournament->game ?: 'Magic: The Gathering')), format: @js(old('format',$tournament->format ?: 'Standard'))}">@csrf @if($tournament->exists) @method('PATCH') @endif
<label class="arena-label">Nome do torneio<input name="title" value="{{ old('title',$tournament->title) }}" required maxlength="255" class="arena-input mt-2">
</label>
<div class="grid gap-4 sm:grid-cols-2">
<label class="arena-label">Jogo<select name="game" x-model="game" @change="format=games[game][0]" class="arena-input mt-2">
@foreach(config('cardgames') as $game=>$formats)<option @selected(old('game',$tournament->game)===$game)>{{ $game }}</option>
@endforeach</select>
</label>
<label class="arena-label">Formato<select name="format" x-model="format" class="arena-input mt-2">
<template x-for="f in games[game]" :key="f">
<option :value="f" :selected="f===format" x-text="f">
</option>
</template>
</select>
</label>
</div>
<label class="arena-label">Data e hora (Brasília)<input type="datetime-local" name="starts_at" value="{{ old('starts_at',$tournament->starts_at?->copy()->timezone('America/Sao_Paulo')->format('Y-m-d\TH:i')) }}" required class="arena-input mt-2">
</label>
<div class="grid gap-4 sm:grid-cols-3">
<label class="arena-label">Premiação<input name="prize" value="{{ old('prize',$tournament->prize) }}" required maxlength="255" class="arena-input mt-2">
</label>
<label class="arena-label">Inscrição (R$)<input type="number" name="entry_fee" value="{{ old('entry_fee',$tournament->entry_fee ?? 0) }}" required min="0" max="999999.99" step="0.01" class="arena-input mt-2">
</label>
<label class="arena-label">Vagas<input type="number" name="slots" value="{{ old('slots',$tournament->slots ?? 16) }}" required min="2" max="256" class="arena-input mt-2">
</label>
</div>
<label class="arena-label">Local<input name="location" value="{{ old('location',$tournament->location) }}" required maxlength="255" class="arena-input mt-2" placeholder="Loja, endereço ou instruções para jogar online">
</label>
<label class="arena-label">Informações e regras<textarea name="description" rows="4" maxlength="5000" class="arena-input mt-2">{{ old('description',$tournament->description) }}</textarea>
</label>
<p class="text-sm text-slate-400">A Arena registra inscrições. Valores de entrada são combinados diretamente com o organizador.</p>
<div class="flex gap-3">
<button class="arena-btn">{{ $tournament->exists ? 'Salvar alterações' : 'Publicar torneio' }}</button>
<a class="arena-btn-secondary" href="{{ route('tournaments.index') }}">Voltar</a>
</div>
</form>
</section>
</x-layouts.app>
