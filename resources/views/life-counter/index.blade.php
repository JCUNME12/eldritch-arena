<x-layouts.app title="Contador de Vida — Eldritch Arena">
    <div x-data="lifeCounter" class="mx-auto max-w-6xl">
        <section x-show="screen === 'setup'" x-cloak>
            <div class="mb-8 text-center">
                <p class="font-bold uppercase tracking-[0.28em] text-arena-cyan">Central de partida</p>
                <h1 class="arena-section-title mt-2 text-3xl md:text-5xl">Contador de Vida</h1>
                <p class="mx-auto mt-3 max-w-2xl text-slate-400">Escolha o jogo e o formato. O Eldritch Arena configura automaticamente a vida, os atalhos e os marcadores corretos.</p>
            </div>

            <div x-show="resumeAvailable" class="arena-card mb-6 flex flex-col gap-4 border-arena-cyan/30 p-5 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <p class="text-sm font-black uppercase tracking-wider text-arena-cyan">Partida em andamento</p>
                    <p class="mt-1 text-sm text-slate-300">Continue exatamente do ponto salvo neste aparelho.</p>
                </div>
                <button type="button" @click="resumeMatch()" class="arena-btn">Continuar partida</button>
            </div>

            <div class="arena-card p-5 sm:p-7">
                <div>
                    <p class="text-xs font-black uppercase tracking-[0.22em] text-slate-500">1. Escolha o jogo</p>
                    <div class="mt-4 grid gap-4 md:grid-cols-2">
                        <template x-for="option in gameOptions" :key="option.id">
                            <button type="button" @click="selectGame(option.id)" class="rounded-3xl border p-5 text-left transition hover:-translate-y-0.5 hover:border-arena-cyan/50" :class="game === option.id ? 'border-arena-purple bg-arena-purple/15 shadow-neon' : 'border-white/10 bg-black/20'">
                                <span class="text-3xl" x-text="option.icon"></span>
                                <span class="mt-3 block font-display text-lg font-black text-white" x-text="option.label"></span>
                                <span class="mt-2 block text-sm leading-6 text-slate-400" x-text="option.description"></span>
                            </button>
                        </template>
                    </div>
                </div>

                <div x-show="game" x-transition class="mt-8 border-t border-white/10 pt-7">
                    <p class="text-xs font-black uppercase tracking-[0.22em] text-slate-500">2. Selecione o formato</p>
                    <div class="mt-4 grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                        <template x-for="format in (formatOptions[game] || [])" :key="format.id">
                            <button type="button" @click="selectFormat(format.id)" class="rounded-2xl border p-4 text-left transition hover:border-arena-cyan/50" :class="formatId === format.id ? 'border-arena-cyan bg-arena-cyan/10' : 'border-white/10 bg-white/[0.03]'">
                                <span class="flex items-start justify-between gap-3">
                                    <span class="font-black text-white" x-text="format.label"></span>
                                    <span class="rounded-full bg-black/30 px-2.5 py-1 text-xs font-black text-arena-cyan" x-text="format.life ? `${format.life} ${game === 'yugioh' ? 'LP' : 'PV'}` : 'Livre'"></span>
                                </span>
                                <span class="mt-2 block text-xs leading-5 text-slate-400" x-text="format.description"></span>
                            </button>
                        </template>
                    </div>
                </div>

                <div x-show="formatId" x-transition class="mt-8 grid gap-5 border-t border-white/10 pt-7 md:grid-cols-2">
                    <label x-show="currentFormat()?.maxPlayers > currentFormat()?.minPlayers" class="block">
                        <span class="arena-label">Quantidade de jogadores</span>
                        <select x-model.number="playerCount" class="arena-input mt-2">
                            <template x-for="count in playerChoices()" :key="count">
                                <option :value="count" x-text="`${count} jogadores`"></option>
                            </template>
                        </select>
                    </label>

                    <label x-show="formatId === 'custom'" class="block">
                        <span class="arena-label">Vida inicial</span>
                        <input type="number" min="1" max="999999" step="1" x-model.number="customLife" class="arena-input mt-2">
                    </label>

                    <div x-show="currentFormat()?.maxPlayers === currentFormat()?.minPlayers" class="arena-card-soft flex items-center justify-between px-4 py-3">
                        <span class="text-sm text-slate-400" x-text="currentFormat()?.teams ? 'Equipes na mesa' : 'Jogadores na mesa'"></span>
                        <strong x-text="currentFormat()?.defaultPlayers"></strong>
                    </div>

                    <label class="arena-card-soft flex cursor-pointer items-center justify-between gap-4 px-4 py-3">
                        <span><strong class="block text-sm">Modo mesa</strong><span class="text-xs text-slate-400">Vira o painel do oponente em celulares.</span></span>
                        <input type="checkbox" x-model="tabletop" class="rounded border-white/20 bg-black/30 text-arena-purple focus:ring-arena-purple">
                    </label>
                </div>

                <div x-show="formatId" class="mt-7 flex justify-end">
                    <button type="button" @click="startMatch()" class="arena-btn w-full px-8 sm:w-auto">Iniciar partida</button>
                </div>
            </div>

            <div class="mt-5 grid gap-3 text-sm text-slate-400 sm:grid-cols-3">
                <div class="arena-card-soft p-4"><strong class="text-white">Salvamento automático</strong><p class="mt-1">A partida sobrevive a recargas e fechamento da página.</p></div>
                <div class="arena-card-soft p-4"><strong class="text-white">Até 30 ações desfeitas</strong><p class="mt-1">Corrija toques acidentais sem perder o placar.</p></div>
                <div class="arena-card-soft p-4"><strong class="text-white">Ferramentas de mesa</strong><p class="mt-1">D6, D20, moeda e escolha aleatória de jogador.</p></div>
            </div>
        </section>

        <template x-teleport="body">
        <section x-show="screen === 'match'" x-cloak class="fixed inset-0 z-[60] flex flex-col overflow-hidden bg-[#05030a]">
            <header class="relative z-20 flex min-h-16 items-center justify-between gap-2 border-b border-white/10 bg-black/80 px-3 py-2 backdrop-blur-xl sm:px-5">
                <button type="button" @click="newMatch()" class="rounded-xl border border-white/10 bg-white/5 px-3 py-2 text-xs font-black text-slate-200 hover:bg-white/10">← Nova</button>
                <div class="min-w-0 text-center">
                    <p class="truncate text-xs font-black text-white" x-text="`${currentGame()?.shortLabel} · ${currentFormat()?.label}`"></p>
                    <p class="font-mono text-xs text-arena-cyan" x-text="elapsed()"></p>
                </div>
                <div class="flex items-center gap-1.5">
                    <button type="button" @click="undoLastChange()" :disabled="undoStack.length === 0" class="rounded-xl border border-white/10 bg-white/5 px-3 py-2 text-xs font-black disabled:cursor-not-allowed disabled:opacity-30" aria-label="Desfazer última alteração">↶</button>
                    <button type="button" @click="toolsOpen = true" class="rounded-xl border border-white/10 bg-white/5 px-3 py-2 text-xs font-black" aria-label="Abrir ferramentas">⌘</button>
                    <button type="button" @click="toggleFullscreen()" class="hidden rounded-xl border border-white/10 bg-white/5 px-3 py-2 text-xs font-black sm:block" aria-label="Alternar tela cheia">⛶</button>
                </div>
            </header>

            <div class="relative flex-1 overflow-y-auto p-2 sm:p-4">
                <div class="mx-auto grid min-h-full max-w-7xl gap-2 sm:gap-4" :class="players.length <= 2 ? 'sm:grid-cols-2' : 'grid-cols-2 xl:grid-cols-3'">
                    <template x-for="(player, index) in players" :key="player.id">
                        <article class="relative flex min-h-[42vh] flex-col overflow-hidden rounded-3xl border border-white/10 p-3 shadow-2xl transition sm:min-h-[460px] sm:p-5" :class="tabletop && players.length === 2 && index === 0 ? 'max-sm:rotate-180' : ''" :style="playerStyle(index)">
                            <div x-show="isDefeated(player)" class="absolute inset-x-0 top-0 z-10 bg-red-500/90 px-3 py-2 text-center text-xs font-black uppercase tracking-widest text-white" x-text="`Eliminado · ${lossReason(player)}`"></div>

                            <div class="flex items-center justify-between gap-2" :class="isDefeated(player) ? 'mt-8' : ''">
                                <div class="min-w-0 flex-1">
                                    <button x-show="editingNameId !== player.id" type="button" @click="beginNameEdit(player.id)" class="max-w-full truncate text-left text-sm font-black text-white sm:text-base" x-text="player.name"></button>
                                    <input x-show="editingNameId === player.id" x-model="player.name" @keydown.enter="applyNameEdit(player.id)" @blur="applyNameEdit(player.id)" maxlength="24" class="w-full rounded-xl border-white/15 bg-black/30 px-3 py-1.5 text-sm font-bold text-white focus:border-arena-cyan focus:ring-arena-cyan">
                                </div>
                                <span class="rounded-full border border-white/10 bg-black/25 px-2.5 py-1 text-[10px] font-black uppercase tracking-wider text-slate-300" x-text="game === 'yugioh' ? 'LP' : 'Vida'"></span>
                            </div>

                            <div class="flex flex-1 flex-col items-center justify-center py-4 text-center sm:py-6">
                                <button x-show="editingLifeId !== player.id" type="button" @click="beginLifeEdit(player.id)" class="max-w-full font-display font-black leading-none tracking-tighter text-white drop-shadow-[0_0_25px_rgba(255,255,255,0.2)]" :class="game === 'yugioh' ? 'text-4xl sm:text-5xl lg:text-7xl' : 'text-6xl sm:text-8xl lg:text-9xl'" x-text="player.life.toLocaleString('pt-BR')" aria-label="Editar total de vida"></button>
                                <form x-show="editingLifeId === player.id" @submit.prevent="applyLifeEdit(player.id)" class="flex w-full max-w-xs items-center gap-2">
                                    <input type="number" x-model.number="lifeEditorValue" class="min-w-0 flex-1 rounded-2xl border-white/15 bg-black/40 px-3 py-3 text-center font-display text-2xl font-black text-white focus:border-arena-cyan focus:ring-arena-cyan">
                                    <button type="submit" class="arena-btn px-4">OK</button>
                                </form>
                                <p class="mt-2 text-[10px] font-bold uppercase tracking-[0.18em] text-white/40">Toque no número para ajustar</p>
                            </div>

                            <div class="grid grid-cols-4 gap-1.5 sm:gap-2">
                                <template x-for="amount in quickAdjustments()" :key="amount">
                                    <button type="button" @click="changeLife(player.id, amount)" class="min-h-14 rounded-2xl border text-base font-black transition active:scale-95 sm:min-h-16 sm:text-xl" :class="amount < 0 ? 'border-red-300/15 bg-red-500/15 text-red-100 hover:bg-red-500/25' : 'border-emerald-300/15 bg-emerald-500/15 text-emerald-100 hover:bg-emerald-500/25'" x-text="signed(amount)" :aria-label="`${signed(amount)} pontos para ${player.name}`"></button>
                                </template>
                            </div>

                            <div x-show="game === 'magic'" class="mt-3 rounded-2xl border border-white/10 bg-black/25 p-2.5">
                                <div class="flex items-center justify-between gap-2">
                                    <span class="text-xs font-black text-emerald-300">☠ Veneno <span class="text-white" x-text="`${player.poison}/${currentFormat()?.poisonLimit}`"></span></span>
                                    <div class="flex gap-1.5">
                                        <button type="button" @click="changePoison(player.id, -1)" class="h-8 w-9 rounded-lg bg-white/10 font-black">−</button>
                                        <button type="button" @click="changePoison(player.id, 1)" class="h-8 w-9 rounded-lg bg-emerald-500/20 font-black text-emerald-200">+</button>
                                    </div>
                                </div>

                                <div x-show="currentFormat()?.commanderDamage" class="mt-2 border-t border-white/10 pt-2">
                                    <button type="button" @click="toggleDetails(player.id)" class="flex w-full items-center justify-between text-left text-xs font-black text-violet-200">
                                        <span>⚔ Dano de comandante <span class="text-white" x-text="commanderMaximum(player)"></span>/21</span>
                                        <span x-text="detailsExpanded(player.id) ? '▲' : '▼'"></span>
                                    </button>
                                    <div x-show="detailsExpanded(player.id)" x-transition class="mt-2 space-y-1.5">
                                        <template x-for="source in players.filter((candidate) => candidate.id !== player.id)" :key="source.id">
                                            <div class="flex items-center justify-between gap-2 rounded-xl bg-white/5 px-2 py-1.5">
                                                <span class="min-w-0 truncate text-[11px] text-slate-300" x-text="`De ${source.name}`"></span>
                                                <div class="flex items-center gap-1.5">
                                                    <button type="button" @click="changeCommanderDamage(player.id, source.id, -1)" class="h-7 w-7 rounded-lg bg-white/10 font-black">−</button>
                                                    <strong class="w-6 text-center text-xs" :class="player.commanderDamage[source.id] >= 21 ? 'text-red-300' : 'text-white'" x-text="player.commanderDamage[source.id]"></strong>
                                                    <button type="button" @click="changeCommanderDamage(player.id, source.id, 1)" class="h-7 w-7 rounded-lg bg-violet-500/20 font-black text-violet-200">+</button>
                                                </div>
                                            </div>
                                        </template>
                                    </div>
                                </div>
                            </div>
                        </article>
                    </template>
                </div>
            </div>

            <button type="button" @click="resetOpen = true" class="absolute bottom-3 left-1/2 z-20 -translate-x-1/2 rounded-full border border-white/15 bg-black/80 px-5 py-2 text-xs font-black text-white shadow-2xl backdrop-blur-xl">↻ Reiniciar</button>

            <div x-show="toolsOpen" x-transition.opacity class="absolute inset-0 z-40 flex items-end bg-black/70 p-3 backdrop-blur-sm sm:items-center sm:justify-center" @click.self="toolsOpen = false">
                <div class="w-full rounded-3xl border border-white/10 bg-arena-panel p-5 shadow-neon sm:max-w-md">
                    <div class="flex items-center justify-between">
                        <div><p class="text-xs font-black uppercase tracking-widest text-arena-cyan">Ferramentas</p><h2 class="mt-1 font-display text-xl font-black">Mesa de jogo</h2></div>
                        <button type="button" @click="toolsOpen = false" class="h-10 w-10 rounded-xl bg-white/5 text-xl">×</button>
                    </div>

                    <div class="mt-5 grid grid-cols-2 gap-3">
                        <button type="button" @click="rollDie(6)" class="arena-btn-secondary">🎲 Rolar D6</button>
                        <button type="button" @click="rollDie(20)" class="arena-btn-secondary">🔮 Rolar D20</button>
                        <button type="button" @click="flipCoin()" class="arena-btn-secondary">🪙 Cara ou coroa</button>
                        <button type="button" @click="randomPlayer()" class="arena-btn-secondary">⇄ Jogador aleatório</button>
                        <button x-show="players.length === 2" type="button" @click="tabletop = !tabletop; save()" class="arena-btn-secondary" x-text="tabletop ? '↕ Desligar modo mesa' : '↕ Ligar modo mesa'"></button>
                        <button type="button" @click="toggleFullscreen()" class="arena-btn-secondary">⛶ Tela cheia</button>
                    </div>

                    <div x-show="toolResult" x-transition class="mt-5 rounded-2xl border border-arena-cyan/30 bg-arena-cyan/10 p-5 text-center">
                        <p class="text-xs font-black uppercase tracking-widest text-arena-cyan" x-text="toolResult?.title"></p>
                        <p class="mt-2 font-display text-4xl font-black text-white" x-text="toolResult?.value"></p>
                    </div>
                </div>
            </div>

            <div x-show="resetOpen" x-transition.opacity class="absolute inset-0 z-50 flex items-center justify-center bg-black/80 p-4 backdrop-blur-sm" @click.self="resetOpen = false">
                <div class="w-full max-w-sm rounded-3xl border border-white/10 bg-arena-panel p-6 text-center shadow-neon">
                    <p class="text-4xl">↻</p>
                    <h2 class="mt-3 font-display text-xl font-black">Reiniciar a partida?</h2>
                    <p class="mt-2 text-sm text-slate-400">Todos os totais e marcadores voltarão aos valores iniciais. Você ainda poderá desfazer.</p>
                    <div class="mt-6 grid grid-cols-2 gap-3">
                        <button type="button" @click="resetOpen = false" class="arena-btn-secondary">Cancelar</button>
                        <button type="button" @click="resetMatch()" class="arena-btn">Reiniciar</button>
                    </div>
                </div>
            </div>
        </section>
        </template>
    </div>
</x-layouts.app>
