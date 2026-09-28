import { test, beforeEach } from "node:test";
import assert from "node:assert/strict";
import { readFileSync } from "node:fs";
import schemeDeck, { shuffle } from "../resources/js/scheme-deck.js";
const catalog = JSON.parse(
    readFileSync(
        new URL("../public/data/archenemy-schemes.json", import.meta.url),
        "utf8",
    ),
);
let stored;
beforeEach(() => {
    stored = null;
    globalThis.localStorage = {
        getItem: () => stored,
        setItem: (_, value) => (stored = value),
    };
});
function ready() {
    const d = schemeDeck();
    d.loadCatalog(catalog);
    d.start();
    return d;
}
test("catalog contains unique real schemes and nine complete thematic decks", () => {
    assert.equal(catalog.cards.length, 102);
    assert.equal(new Set(catalog.cards.map((c) => c.id)).size, 102);
    assert.equal(
        catalog.decks.filter((d) => d.kind === "Baralhos temáticos").length,
        9,
    );
    for (const deck of catalog.decks) {
        assert.ok(deck.cards.length >= 10);
        assert.equal(new Set(deck.cards).size, deck.cards.length);
        assert.ok(
            deck.cards.every((id) => catalog.cards.some((c) => c.id === id)),
        );
    }
    for (const card of catalog.cards) {
        assert.ok(card.text);
        assert.match(card.image, /^https:\/\/cards\.scryfall\.io\//);
    }
});
test("shuffle is a permutation without changing the source", () => {
    const ids = [1, 2, 3, 4];
    const result = shuffle(ids, () => 0);
    assert.deepEqual(ids, [1, 2, 3, 4]);
    assert.notDeepEqual(result, ids);
    assert.deepEqual([...result].sort(), ids);
});
test("deck begins face down and reveals the first shuffled card only on request", () => {
    const d = ready();
    const first = d.queue[0];
    assert.equal(d.current, null);
    d.reveal();
    assert.equal(d.current, first);
    const next = d.queue[0];
    d.reveal();
    assert.equal(d.queue[0], next);
});
test("resolved normal schemes go to bottom while ongoing schemes stay out until abandoned", () => {
    const d = ready();
    const normal = d.queue.find((id) => !d.cards[id].ongoing);
    d.queue = [normal, ...d.queue.filter((id) => id !== normal)];
    d.reveal();
    d.next();
    assert.equal(d.queue.at(-1), normal);
    assert.equal(d.current, null);
    const ongoing = d.queue.find((id) => d.cards[id].ongoing);
    d.queue = [ongoing, ...d.queue.filter((id) => id !== ongoing)];
    d.reveal();
    d.next();
    assert.deepEqual(d.ongoing, [ongoing]);
    assert.ok(!d.queue.includes(ongoing));
    d.abandon(ongoing);
    assert.equal(d.queue.at(-1), ongoing);
    assert.equal(d.ongoing.length, 0);
    assert.ok(d.validState(d.state()));
});
test("undo restores reveal, resolution and abandonment with no lost cards", () => {
    const d = ready();
    const initial = d.state();
    d.reveal();
    const revealed = d.state();
    d.next();
    d.undo();
    assert.deepEqual(d.state(), revealed);
    d.undo();
    assert.deepEqual(d.state(), initial);
});
test("refresh keeps card face, full deck order and ongoing cards", () => {
    const d = ready();
    d.reveal();
    const restored = schemeDeck();
    restored.loadCatalog(catalog);
    assert.deepEqual(restored.state(), d.state());
    assert.equal(restored.setup, false);
});
test("changing a running deck requires confirmation and clears old ongoing cards", () => {
    const d = ready();
    d.reveal();
    const initial = d.state();
    d.selected = catalog.decks[1].id;
    d.start();
    assert.deepEqual(d.state(), initial);
    assert.equal(d.pending, true);
    d.start();
    assert.equal(d.deckId, d.selected);
    assert.equal(d.current, null);
    assert.equal(d.ongoing.length, 0);
    assert.ok(d.validState(d.state()));
});
test("tampered duplicate saved deck is rejected, storage failure does not stop play", () => {
    const d = ready();
    const state = d.state();
    state.queue[0] = state.queue[1];
    stored = JSON.stringify(state);
    const restored = schemeDeck();
    restored.loadCatalog(catalog);
    assert.equal(restored.setup, true);
    assert.ok(restored.storageError);
    globalThis.localStorage.setItem = () => {
        throw Error("quota");
    };
    d.reveal();
    assert.ok(d.current);
    assert.ok(d.storageError);
});
test("exhausted deck with all cards active cannot reveal until abandonment", () => {
    const d = ready();
    const id = d.queue.find((id) => d.cards[id].ongoing);
    d.queue = [];
    d.current = null;
    d.ongoing = [id];
    d.reveal();
    assert.equal(d.current, null);
    d.abandon(id);
    d.reveal();
    assert.equal(d.current, id);
});
test("catalog network failure is visible and retry can recover", async () => {
    const previous = globalThis.fetch;
    try {
        globalThis.fetch = async () => ({ ok: false });
        const d = schemeDeck();
        await d.init();
        assert.ok(d.error);
        assert.equal(d.loading, false);
        globalThis.fetch = async () => ({
            ok: true,
            json: async () => catalog,
        });
        d.error = "";
        await d.init();
        assert.ok(d.catalog);
        assert.equal(d.error, "");
    } finally {
        globalThis.fetch = previous;
    }
});
