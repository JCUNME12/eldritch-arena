<x-layouts.app title="Mesa de jogo — Eldritch Arena">
<div x-data="lifeCounter" class="game-table" @keydown.escape.window="settings=false;tools=false;historyOpen=false;active=null">
 <header class="table-toolbar">
<div>
<p class="eyebrow">COMPANHEIRO DE MESA</p>
<h1 class="text-2xl font-bold">Marcador de vida</h1>
<p class="text-sm text-slate-400" x-text="presets[format].name">
</p>
</div>
<div class="flex flex-wrap gap-2">
<button class="arena-btn-secondary" @click="openSettings()">Nova mesa</button>
<button class="arena-btn-secondary" @click="undo()" :disabled="!history.length">Desfazer</button>
<button class="arena-btn-secondary" @click="tools=!tools">Dados e moeda</button>
<button class="arena-btn-secondary" @click="fullscreen()">Tela cheia</button>
<a x-show="format==='archenemy'" class="arena-btn-secondary" href="#scheme-deck">Esquemas</a>
</div>
</header>
 <div class="arena-card p-4 mb-4 text-sm text-slate-300" x-show="presets[format].description">
 <p x-text="'Referência do formato: '+presets[format].description"></p>
 <p class="mt-2" x-show="initial!==presets[format].life" x-text="'Pontos iniciais personalizados nesta mesa: '+initial+(presets[format].leaderLife ? ' por aliado; arqui-inimigo: '+presets[format].leaderLife : '')"></p>
 </div>
 <p x-show="storageError" x-cloak class="p-3 text-amber-200">Não foi possível restaurar ou salvar a mesa neste navegador.</p>
 <p x-show="migrationNotice" x-cloak class="p-3 text-amber-200" x-text="migrationNotice"></p>
 <div class="players-grid" :class="'players-'+players.length">
 <template x-for="(player,index) in players" :key="player.id">
<section class="player-zone" :style="{'--player-color':player.color,'--digits':Math.max(2,String(player.life).length)}" :class="{'player-rotated':player.rotated}">
 <div class="player-content">
<div class="flex items-center justify-between gap-2">
<span class="player-number" x-text="String(index+1).padStart(2,'0')">
</span>
<input class="player-name" :aria-label="'Nome do jogador '+(index+1)" :value="player.name" maxlength="30" @change="updatePlayer(index,'name',$event.target.value);$el.value=player.name">
<button class="counter-small" @click="active=index" :aria-label="'Configurar '+player.name">•••</button>
</div>
 <p class="mt-3 text-center text-xs" x-text="playerRole(index)"></p>
 <div class="life-controls">
<button @click="change(index,-step)" :aria-label="'Diminuir vida de '+player.name">−</button>
<output class="life-total" x-text="player.life" :aria-label="'Vida de '+player.name" aria-live="polite">
</output>
<button @click="change(index,step)" :aria-label="'Aumentar vida de '+player.name">+</button>
</div>
 <div class="flex justify-center gap-3">
<button class="counter-small" @click="change(index,-step*5)" x-text="'−'+step*5">
</button>
<span class="self-center text-xs opacity-70" x-text="'PASSO '+step">
</span>
<button class="counter-small" @click="change(index,step*5)" x-text="'+'+step*5">
</button>
</div>
 <div class="mt-3 flex justify-center gap-4 text-sm" x-show="presets[format].magic">
<span x-text="'Veneno '+player.poison">
</span>
<span x-text="'Energia '+player.energy">
</span>
<span x-show="presets[format].commanderDamage" x-text="'Experiência '+player.experience">
</span>
</div>
 <p class="mt-2 text-center text-sm font-bold text-amber-100" x-text="warning(player)">
</p>
</div>
</section>
</template>
 </div>
 <template x-if="format==='archenemy'"><div id="scheme-deck">@include('life-counter.schemes')</div></template>
 <footer class="table-footer">
<span>Salvo neste navegador · funciona sem conexão após abrir a mesa</span>
<div class="flex gap-4">
<button @click="historyOpen=!historyOpen">Histórico</button>
<button @click="reset()" x-text="pendingReset ? 'Confirmar reinício' : 'Reiniciar pontos'">
</button>
<button x-show="pendingReset" @click="pendingReset=false">Manter partida</button>
</div>
</footer>
 <section x-show="historyOpen" x-cloak class="arena-card p-5 mt-3">
<h2 class="font-bold">Últimas ações desta sessão</h2>
<p x-show="!history.length" class="text-slate-400">Nenhuma alteração ainda.</p>
<template x-for="(entry,i) in history" :key="i">
<p class="py-2 border-b border-white/10 text-sm" x-text="entry.label">
</p>
</template>
</section>
 <section x-show="tools" x-cloak class="arena-card p-5 mt-3">
<div class="flex justify-between">
<h2 class="font-bold">Sorteio da mesa</h2>
<button @click="tools=false">Fechar</button>
</div>
<div class="flex flex-wrap gap-2 mt-4">
<template x-for="n in [4,6,8,10,12,20]">
<button class="arena-btn-secondary" @click="dice(n)" x-text="'D'+n">
</button>
</template>
<button x-show="presets[format].planar" class="arena-btn-secondary" @click="planarDie()">Dado planar</button>
<button class="arena-btn-secondary" @click="coin()">Moeda</button>
<button class="arena-btn" @click="randomPlayer()">Sortear jogador</button>
</div>
<p class="text-3xl font-bold mt-4" aria-live="polite" x-text="result">
</p>
</section>
 <div x-show="settings" x-cloak class="table-overlay" @click.self="settings=false">
<section role="dialog" aria-modal="true" aria-label="Nova mesa" class="table-dialog" @keydown.tab="trapFocus($event)" x-effect="if(settings) { const step=setupStep; $nextTick(() => $el.querySelector(step==='game' ? '[data-game-choice]' : 'select').focus()) }">
<h2 class="text-2xl font-bold">Prepare sua mesa</h2>
<div x-show="setupStep==='game'">
<p class="text-slate-400 mt-2">1 de 2 · Qual jogo vocês vão jogar?</p>
<div class="grid gap-3 mt-5">
<button data-game-choice class="arena-btn-secondary p-5 text-left" @click="chooseGame('magic')">Magic: The Gathering</button>
<button class="arena-btn-secondary p-5 text-left" @click="chooseGame('ygo')">Yu-Gi-Oh!</button>
</div>
<button class="mt-5 text-sm text-slate-400 underline" @click="chooseGame('custom')">Criar uma mesa personalizada</button>
<div class="mt-6"><button class="arena-btn-secondary" @click="settings=false">Cancelar</button></div>
</div>
<div x-show="setupStep==='format'">
<p class="text-slate-400 mt-2" x-text="'2 de 2 · '+({magic:'Magic: The Gathering',ygo:'Yu-Gi-Oh!',custom:'Mesa personalizada'}[draftGame])"></p>
<button class="mt-3 text-sm underline" @click="setupStep='game';pendingStart=false">Trocar jogo</button>
<label class="arena-label block mt-5">Formato<select class="arena-input mt-2" x-model="draftFormat" @change="choosePreset()">
<template x-for="(preset,id) in availablePresets()" :key="id">
<option :value="id" :selected="id===draftFormat" x-text="preset.name.replace(/^Magic · |^Yu-Gi-Oh! · /,'')">
</option>
</template>
</select>
</label>
<p class="mt-4 text-sm text-slate-300" x-text="presets[draftFormat].description"></p>
<div class="grid grid-cols-2 gap-4 mt-4">
<label class="arena-label">Jogadores<select class="arena-input mt-2" x-model.number="draftCount" :disabled="!!presets[draftFormat].fixedCount" @change="pendingStart=false">
<template x-for="n in availableCounts()">
<option :value="n" :selected="n===draftCount" x-text="n">
</option>
</template>
</select>
</label>
<label class="arena-label"><span x-text="presets[draftFormat].leaderLife ? 'Vida de cada aliado' : ['two_headed','archenemy_shared'].includes(presets[draftFormat].layout) ? 'Vida por equipe' : 'Vida inicial'"></span><input class="arena-input mt-2" type="number" min="1" max="99999" x-model.number="draftLife" @input="pendingStart=false">
</label>
</div>
<p class="mt-4 text-sm text-slate-400">Os presets definem pontos iniciais. A mesa não valida decks nem substitui as regras do jogo.</p>
<p x-show="pendingStart" role="alert" class="mt-4 text-amber-200">A partida atual será substituída. Confirme para continuar.</p>
<div class="flex gap-3 mt-6">
<button class="arena-btn" @click="start()" x-text="pendingStart ? 'Confirmar nova partida' : 'Iniciar mesa'">
</button>
<button class="arena-btn-secondary" @click="settings=false">Voltar</button>
</div>
</div>
</section>
</div>
 <template x-if="active!==null">
<div class="table-overlay" @click.self="active=null">
<section class="table-dialog" role="dialog" aria-modal="true" aria-label="Marcadores do jogador" @keydown.tab="trapFocus($event)" x-init="$nextTick(() => $el.querySelector('button').focus())">
<div class="flex justify-between gap-3">
<h2 class="text-2xl font-bold" x-text="players[active].name">
</h2>
<button class="arena-btn-secondary" @click="active=null">Fechar</button>
</div>
<div class="flex flex-wrap gap-2 my-4">
<template x-for="color in colors">
<button class="color-choice" :style="{background:color}" :aria-label="'Usar cor '+color" @click="updatePlayer(active,'color',color)">
</button>
</template>
<button class="arena-btn-secondary" @click="updatePlayer(active,'rotated',!players[active].rotated)">Girar 180°</button>
</div>
 <label class="arena-label">Ajuste de vida<input type="number" min="1" max="99999" x-model.number="customAmount" class="arena-input my-2">
</label>
<div class="flex gap-2">
<button class="arena-btn-secondary" @click="change(active,-Math.abs(customAmount))">Subtrair</button>
<button class="arena-btn-secondary" @click="change(active,Math.abs(customAmount))">Adicionar</button>
</div>
 <template x-if="presets[format].magic">
<div class="mt-5">
<template x-for="[field,label] in [['poison','Veneno'],['energy','Energia'],['experience','Experiência']]">
<div class="marker-row">
<span x-text="label">
</span>
<button class="counter-small" @click="change(active,-1,field)" :aria-label="'Diminuir '+label">−</button>
<output x-text="players[active][field]">
</output>
<button class="counter-small" @click="change(active,1,field)" :aria-label="'Aumentar '+label">+</button>
</div>
</template>
</div>
</template>
 <div x-show="presets[format].commanderDamage" class="mt-5">
<h3 class="font-bold">Dano de comandante recebido</h3>
<p class="text-sm text-slate-400 my-2">Registre somente dano de combate. Cada comandante é contado separadamente por jogador. O dano também reduz a vida, inclusive a compartilhada. C1 e C2 permitem parceiros.</p>
<template x-for="(source,i) in players">
<div>
<template x-for="slot in [0,1]">
<div class="marker-row">
<span class="text-sm" x-text="source.name+' · C'+(slot+1)">
</span>
<button class="counter-small" @click="damage(active,i,slot,-1)" aria-label="Remover dano de comandante">−</button>
<output x-text="players[active].commander[i][slot]">
</output>
<button class="counter-small" @click="damage(active,i,slot,1)" aria-label="Adicionar dano de comandante">+</button>
</div>
</template>
</div>
</template>
</div>
 </section>
</div>
</template>
</div>
</x-layouts.app>
