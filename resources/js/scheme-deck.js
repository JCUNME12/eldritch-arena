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
        selected: "",
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
        dock() {
            if (!this.$nextTick) return;
            this.$nextTick(() => {
                const panel = this.$refs.schemePanel;
                const reduced = window.matchMedia("(prefers-reduced-motion: reduce)").matches;
                panel.scrollIntoView({ behavior: reduced ? "instant" : "smooth", block: "start" });
                if (!reduced) panel.animate([
                    { transform: "translateY(-90px)", opacity: 0 },
                    { transform: "translateY(0)", opacity: 1 }
                ], { duration: 550, easing: "cubic-bezier(.2,.8,.2,1)" });
                this.$refs.advanceScheme.focus({ preventScroll: true });
            });
        },
        closeSetup() {
            this.pending = false;
            this.setup = false;
            this.$nextTick?.(() => this.$refs.changeDeck.focus({ preventScroll: true }));
        },
        trapSetupFocus(event) {
            const controls = [...event.currentTarget.querySelectorAll("button,select,a[href]")]
                .filter(el => !el.disabled && el.offsetParent !== null);
            const first = controls[0], last = controls.at(-1);
            if (event.shiftKey && document.activeElement === first) { event.preventDefault(); last.focus(); }
            else if (!event.shiftKey && document.activeElement === last) { event.preventDefault(); first.focus(); }
        },
        advance() {
            if (!this.current && !this.queue.length) return;
            this.checkpoint();
            if (this.current) {
                if (this.cards[this.current].ongoing) this.ongoing.push(this.current);
                else this.queue.push(this.current);
            }
            this.current = this.queue.shift() || null;
            this.imageFailed = false;
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
            this.dock();
        },
        reveal() {
            if (this.current || !this.queue.length) return;
            this.checkpoint();
            this.current = this.queue.shift();
            this.imageFailed = false;
        },
        next() {
            if (!this.current) return;
            this.checkpoint();
            if (this.cards[this.current].ongoing)
                this.ongoing.push(this.current);
            else this.queue.push(this.current);
            this.current = null;
            this.imageFailed = false;
        },
        abandon(id) {
            if (!this.ongoing.includes(id)) return;
            this.checkpoint();
            this.ongoing = this.ongoing.filter((value) => value !== id);
            this.queue.push(id);
        },
        undo() {
            const state = this.history.shift();
            if (state) {
                this.restore(state);
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
            this.selected = "";
            this.pending = false;
            this.setup = true;
        },
    };
}
