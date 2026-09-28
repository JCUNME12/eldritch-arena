const storageKey = "eldritch.schemes.v1";
export function shuffle(ids, randomIndex = randomBelow) {
    const result = [...ids];
    for (let i = result.length - 1; i > 0; i--) {
        const j = randomIndex(i + 1);
        [result[i], result[j]] = [result[j], result[i]];
    }
    return result;
}
function randomBelow(max) {
    const values = new Uint32Array(1);
    const limit = Math.floor(4294967296 / max) * max;
    do {
        crypto.getRandomValues(values);
    } while (values[0] >= limit);
    return values[0] % max;
}
export default function schemeDeck() {
    return {
        catalog: null,
        cards: {},
        loading: true,
        error: "",
        storageError: "",
        selected: "all",
        deckId: null,
        queue: [],
        current: null,
        ongoing: [],
        history: [],
        setup: true,
        pending: false,
        imageFailed: false,
        async init() {
            try {
                const response = await fetch("/data/archenemy-schemes.json");
                if (!response.ok) throw new Error("catalog");
                this.loadCatalog(await response.json());
            } catch {
                this.error =
                    "Não foi possível carregar o catálogo. Verifique a conexão e tente novamente.";
            } finally {
                this.loading = false;
            }
        },
        loadCatalog(catalog) {
            if (
                !Array.isArray(catalog.cards) ||
                !Array.isArray(catalog.decks) ||
                !catalog.cards.length
            )
                throw new Error("catalog");
            this.catalog = catalog;
            this.cards = Object.fromEntries(
                catalog.cards.map((card) => [card.id, card]),
            );
            try {
                const saved = JSON.parse(localStorage.getItem(storageKey));
                if (saved && this.validState(saved)) {
                    this.restore(saved);
                    this.selected = this.deckId;
                    this.setup = false;
                } else if (saved)
                    this.storageError =
                        "O baralho salvo não pôde ser restaurado. Escolha uma coleção para começar novamente.";
            } catch {
                this.storageError =
                    "Não foi possível ler o baralho salvo neste navegador.";
            }
        },
        validState(state) {
            const deck = this.catalog.decks.find((d) => d.id === state.deckId);
            if (
                !deck ||
                !Array.isArray(state.queue) ||
                !Array.isArray(state.ongoing) ||
                !(state.current === null || typeof state.current === "string")
            )
                return false;
            const ids = [
                ...state.queue,
                ...state.ongoing,
                ...(state.current ? [state.current] : []),
            ];
            return (
                ids.length === deck.cards.length &&
                new Set(ids).size === ids.length &&
                ids.every((id) => deck.cards.includes(id)) &&
                state.ongoing.every((id) => this.cards[id]?.ongoing)
            );
        },
        state() {
            return {
                deckId: this.deckId,
                queue: [...this.queue],
                current: this.current,
                ongoing: [...this.ongoing],
            };
        },
        restore(state) {
            this.deckId = state.deckId;
            this.queue = [...state.queue];
            this.current = state.current;
            this.ongoing = [...state.ongoing];
            this.imageFailed = false;
        },
        save() {
            try {
                localStorage.setItem(storageKey, JSON.stringify(this.state()));
            } catch {
                this.storageError =
                    "Não foi possível salvar. Você pode continuar, mas a partida pode se perder ao fechar esta página.";
            }
        },
        checkpoint() {
            this.history.unshift(this.state());
            this.history = this.history.slice(0, 30);
        },
        start() {
            const deck = this.catalog?.decks.find(
                (d) => d.id === this.selected,
            );
            if (!deck) return;
            if (this.deckId && !this.pending) {
                this.pending = true;
                return;
            }
            this.deckId = deck.id;
            this.queue = shuffle(deck.cards);
            this.current = null;
            this.ongoing = [];
            this.history = [];
            this.setup = false;
            this.pending = false;
            this.imageFailed = false;
            this.save();
        },
        reveal() {
            if (this.current || !this.queue.length) return;
            this.checkpoint();
            this.current = this.queue.shift();
            this.imageFailed = false;
            this.save();
        },
        next() {
            if (!this.current) return;
            this.checkpoint();
            if (this.cards[this.current].ongoing)
                this.ongoing.push(this.current);
            else this.queue.push(this.current);
            this.current = null;
            this.imageFailed = false;
            this.save();
        },
        abandon(id) {
            if (!this.ongoing.includes(id)) return;
            this.checkpoint();
            this.ongoing = this.ongoing.filter((value) => value !== id);
            this.queue.push(id);
            this.save();
        },
        undo() {
            const state = this.history.shift();
            if (state) {
                this.restore(state);
                this.save();
            }
        },
        deckName() {
            return (
                this.catalog?.decks.find((d) => d.id === this.deckId)?.name ||
                ""
            );
        },
        selectionCount() {
            return (
                this.catalog?.decks.find((d) => d.id === this.selected)?.cards
                    .length || 0
            );
        },
        openSetup() {
            this.selected = this.deckId || "all";
            this.pending = false;
            this.setup = true;
        },
    };
}
