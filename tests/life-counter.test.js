import { test, beforeEach } from "node:test";
import assert from "node:assert/strict";
import counter, { newPlayers, presets } from "../resources/js/life-counter.js";
let saved;
beforeEach(() => {
    saved = null;
    globalThis.localStorage = {
        getItem: () => saved,
        setItem: (_, v) => (saved = v),
    };
});
test("Commander defaults to four players with 40 life, and a fresh table is confirmed", () => {
    const c = counter();
    c.draftFormat = "commander";
    c.choosePreset();
    c.start();
    assert.equal(c.players.length, 2);
    c.start();
    assert.equal(c.players.length, 4);
    assert.equal(c.players[0].life, 40);
});
test("life can go negative, undo restores it, refresh restores saved state", () => {
    const c = counter();
    c.change(0, -25);
    assert.equal(c.players[0].life, -5);
    const restored = counter();
    restored.init();
    assert.equal(restored.players[0].life, -5);
    c.undo();
    assert.equal(c.players[0].life, 20);
});
test("commander damage is separate per source and partner and changes life exactly once", () => {
    const c = counter();
    c.format = "commander";
    c.players = newPlayers(4, 40);
    c.damage(0, 1, 0, 20);
    c.damage(0, 1, 1, 1);
    assert.equal(c.players[0].life, 19);
    assert.equal(c.warning(c.players[0]), "");
    c.damage(0, 1, 0, 1);
    assert.match(c.warning(c.players[0]), /21/);
    c.undo();
    assert.equal(c.players[0].life, 19);
    c.damage(0, 2, 0, -1);
    assert.equal(c.players[0].life, 19);
});
test("poison markers never go below zero and warn at ten", () => {
    const c = counter();
    c.change(0, -1, "poison");
    assert.equal(c.players[0].poison, 0);
    c.change(0, 10, "poison");
    assert.match(c.warning(c.players[0]), /10/);
});
test("Yu-Gi-Oh presets use LP and proper increments", () => {
    const c = counter();
    c.draftFormat = "ygo";
    c.choosePreset();
    c.pendingStart = true;
    c.start();
    assert.equal(c.step, 100);
    assert.equal(c.initial, 8000);
    assert.equal(presets.speed.life, 4000);
});
test("reset preserves names and rotation; undo restores full game", () => {
    const c = counter();
    c.players[0].name = "Joao";
    c.players[0].rotated = true;
    c.change(0, -3);
    c.reset();
    assert.equal(c.players[0].life, 17);
    c.reset();
    assert.equal(c.players[0].life, 20);
    assert.equal(c.players[0].name, "Joao");
    assert.equal(c.players[0].rotated, true);
    c.undo();
    assert.equal(c.players[0].life, 17);
});
test("corrupt storage recovers and unavailable storage does not break controls", () => {
    saved = "{broken";
    const c = counter();
    c.init();
    assert.equal(c.players.length, 2);
    globalThis.localStorage.setItem = () => {
        throw Error("quota");
    };
    c.change(0, 1);
    assert.equal(c.players[0].life, 21);
    assert.equal(c.storageError, true);
});
test("dice remain in range", () => {
    const c = counter();
    for (const sides of [4, 6, 8, 10, 12, 20])
        for (let i = 0; i < 40; i++) {
            const n = c.roll(sides);
            assert.ok(n >= 0 && n < sides);
        }
});

test("personalization participates in undo without losing later names", () => {
    const c = counter();
    c.change(0, -1);
    c.updatePlayer(0, "name", "Joao");
    c.undo();
    assert.equal(c.players[0].name, "Jogador 1");
    assert.equal(c.players[0].life, 19);
    c.undo();
    assert.equal(c.players[0].life, 20);
});

test("a new table clears a pending reset confirmation", () => {
    const c = counter();
    c.reset();
    c.pendingStart = true;
    c.start();
    c.change(0, -2);
    c.reset();
    assert.equal(c.players[0].life, 18);
});
