<section x-data="schemeDeck" class="arena-card p-5 md:p-8 mt-6" aria-label="Baralho de esquemas">
    <div class="flex flex-wrap items-center justify-between gap-3 mb-5">
        <div><p class="eyebrow">O PRÓXIMO PASSO DO ARQUI-INIMIGO</p><h2 class="text-2xl font-bold">Esquemas</h2></div>
        <button x-show="deckId && !setup" class="arena-btn-secondary" @click="openSetup()">Trocar baralho</button>
    </div>
    <p x-show="loading" role="status">Carregando coleções…</p>
    <div x-show="error" x-cloak role="alert"><p x-text="error"></p><button class="arena-btn-secondary mt-3" @click="error='';loading=true;init()">Tentar novamente</button></div>
    <p x-show="storageError" x-cloak class="text-amber-200 mb-4" role="alert" x-text="storageError"></p>
    <template x-if="catalog">
    <div>
        <div x-show="setup">
            <p class="text-slate-300">Escolha uma coleção, um baralho temático ou misture todos os esquemas.</p>
            <label class="arena-label block mt-5">Coleção ou baralho
                <select class="arena-input mt-2" x-model="selected" @change="pending=false">
                    <template x-for="deck in catalog.decks" :key="deck.id"><option :value="deck.id" :selected="deck.id===selected" x-text="deck.kind+' · '+deck.name"></option></template>
                </select>
            </label>
            <p class="text-sm text-slate-400 mt-3"><span x-text="selectionCount()"></span> esquemas distintos · embaralhados ao começar · cartas em inglês</p>
            <p class="text-sm text-slate-400 mt-2">Baralhos antigos foram adaptados sem repetições, conforme a regra de Archenemy Commander. Esta ferramenta revela cartas; vocês resolvem seus efeitos.</p>
            <p x-show="pending" role="alert" class="text-amber-200 mt-4">Isso substituirá o baralho em andamento e seus esquemas ativos.</p>
            <div class="flex flex-wrap gap-3 mt-5">
                <button class="arena-btn" @click="start()" x-text="pending ? 'Confirmar novo baralho' : 'Embaralhar e começar'"></button>
                <button x-show="deckId" class="arena-btn-secondary" @click="setup=false;pending=false">Continuar baralho atual</button>
            </div>
        </div>
        <div x-show="!setup">
            <div class="flex flex-wrap justify-between gap-3 mb-5">
                <div><h3 class="font-bold" x-text="deckName()"></h3><p class="text-sm text-slate-400"><span x-text="queue.length"></span> no baralho · <span x-text="ongoing.length"></span> contínuos ativos</p></div>
                <button class="arena-btn-secondary" @click="undo()" :disabled="!history.length">Desfazer esquema</button>
            </div>
            <div class="scheme-stage">
                <button x-show="!current && queue.length" class="scheme-back" @click="reveal()" aria-label="Revelar próximo esquema">
                    <span class="scheme-sigil" aria-hidden="true">E</span><span class="eyebrow">ELDRITCH ARENA</span><strong>ARCHENEMY</strong><span>Toque para revelar o próximo esquema</span>
                </button>
                <p x-show="!current && !queue.length" class="text-slate-300">Todos os esquemas estão ativos. Abandone um esquema quando suas regras permitirem para devolvê-lo ao baralho.</p>
                <template x-if="current">
                    <article class="scheme-revealed" aria-live="polite">
                        <img x-show="!imageFailed" :src="cards[current].image" :alt="cards[current].name" x-on:error="imageFailed=true" class="scheme-image" referrerpolicy="no-referrer">
                        <div class="scheme-rules">
                            <span class="eyebrow" x-text="cards[current].ongoing ? 'ESQUEMA CONTÍNUO' : 'ESQUEMA'"></span>
                            <h3 class="text-xl font-bold mt-2" x-text="cards[current].name"></h3>
                            <p x-show="imageFailed" class="text-amber-200 mt-3">Imagem indisponível. O texto da carta continua acessível abaixo.</p>
                            <p class="whitespace-pre-line mt-4 text-slate-200" x-text="cards[current].text"></p>
                            <a class="text-sm underline block mt-4" :href="cards[current].url" target="_blank" rel="noopener noreferrer">Ver carta e informações no Scryfall</a>
                            <button class="arena-btn mt-5" @click="next()" x-text="cards[current].ongoing ? 'Manter ativo e preparar próximo' : 'Resolvido · preparar próximo'"></button>
                            <p class="text-xs text-slate-400 mt-3">Revele no início da primeira fase principal do arqui-inimigo. Avance depois de resolver o esquema; efeitos especiais são controlados pela mesa.</p>
                        </div>
                    </article>
                </template>
            </div>
            <section x-show="ongoing.length" class="mt-6">
                <h3 class="text-xl font-bold">Esquemas contínuos ativos</h3>
                <p class="text-sm text-slate-400 mt-2">Permanecem fora do baralho até serem abandonados. Confira a condição na carta.</p>
                <div class="grid gap-4 mt-4 md:grid-cols-2">
                    <template x-for="id in ongoing" :key="id">
                        <article class="rounded-xl border border-white/10 p-4">
                            <h4 class="font-bold" x-text="cards[id].name"></h4>
                            <details class="mt-3"><summary class="cursor-pointer text-sm">Ver carta e efeito</summary><img class="scheme-image mt-3" :src="cards[id].image" :alt="cards[id].name" loading="lazy" referrerpolicy="no-referrer"><p class="whitespace-pre-line text-sm mt-3" x-text="cards[id].text"></p></details>
                            <button class="arena-btn-secondary mt-4" @click="abandon(id)" :aria-label="'Abandonar '+cards[id].name">Abandonar · devolver ao fundo</button>
                        </article>
                    </template>
                </div>
            </section>
            <p class="text-xs text-slate-400 mt-5">Baralho salvo neste navegador. Revelar não repete cartas ainda na fila. Esquemas resolvidos voltam ao fundo; contínuos só voltam quando abandonados. Desfazer está disponível nesta sessão.</p>
        </div>
        <p class="text-xs text-slate-400 mt-6">Catálogo: <span x-text="catalog.updated"></span> · Dados e imagens: <a href="https://scryfall.com" target="_blank" rel="noopener noreferrer" class="underline">Scryfall</a> · Listas: <a href="https://mtgjson.com" target="_blank" rel="noopener noreferrer" class="underline">MTGJSON</a>. Magic: The Gathering e suas cartas são © Wizards of the Coast. Ferramenta de fãs, sem afiliação. Imagens precisam de conexão.</p>
    </div>
    </template>
</section>
