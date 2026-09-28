import { test, beforeEach } from "node:test";
import assert from "node:assert/strict";
import counter, { newPlayers, presets } from "../resources/js/life-counter.js";
let saved;
test("old Commander Archenemy tables restore under the unified name", () => {
    saved = JSON.stringify({
        format: "archenemy_commander",
        initial: 60,
        players: newPlayers(4, 60),
    });
    const c = counter();
    c.init();
    assert.equal(c.format, "archenemy");
    assert.equal(c.initial, 60);
});
test("old classic Archenemy sums allied remaining life without losing player counters", () => {
    const players = newPlayers(4, 20);
    players[0].life = 35;
    players[1].life = 12;
    players[2].poison = 4;
    saved = JSON.stringify({ format: "archenemy", initial: 20, players });
    const c = counter();
    c.init();
    assert.deepEqual(
        c.players.map((p) => p.life),
        [35, 52, 52, 52],
    );
    assert.equal(c.players[2].poison, 4);
    c.change(1, -2);
    const restored = counter();
    restored.init();
    assert.deepEqual(
        restored.players.map((p) => p.life),
        [35, 50, 50, 50],
    );
    restored.reset();
    restored.reset();
    assert.deepEqual(
        restored.players.map((p) => p.life),
        [60, 60, 60, 60],
    );
});
test("game selection filters formats and resets incompatible defaults without changing the active table", () => {
    const c = table("commander");
    c.openSettings();
    assert.equal(c.setupStep, "game");
    c.chooseGame("ygo");
    assert.deepEqual(Object.keys(c.availablePresets()), ["ygo", "speed"]);
    assert.equal(c.draftLife, 8000);
    assert.equal(c.format, "commander");
    c.chooseGame("magic");
    assert.ok(!Object.hasOwn(c.availablePresets(), "ygo"));
    assert.equal(c.draftLife, 20);
    c.chooseGame("custom");
    assert.deepEqual(Object.keys(c.availablePresets()), ["custom"]);
});
test("reopening a saved Yu-Gi-Oh table preserves its chosen format and points", () => {
    const c = table("speed");
    c.openSettings();
    c.chooseGame("ygo");
    assert.equal(c.draftFormat, "speed");
    assert.equal(c.draftLife, 4000);
});
test("coin and player draw use both possible faces and select a valid player", () => {
    const c = counter();
    c.roll = () => 0;
    c.coin();
    assert.equal(c.result, "Coroa");
    c.roll = () => 1;
    c.coin();
    assert.equal(c.result, "Cara");
    c.randomPlayer();
    assert.equal(c.result, "Começa: Jogador 2");
});
function table(format) {
    const c = counter();
    c.draftFormat = format;
    c.choosePreset();
    c.start();
    c.start();
    return c;
}
test("every preset starts, persists and resets without losing its format", () => {
    for (const format of Object.keys(presets)) {
        const c = table(format);
        assert.equal(c.format, format);
        c.change(0, -3);
        const restored = counter();
        restored.init();
        assert.deepEqual(restored.players, c.players);
        assert.equal(restored.format, format);
        restored.reset();
        restored.reset();
        assert.equal(
            restored.players[0].life,
            presets[format].leaderLife || presets[format].life,
        );
    }
});
test("Two-Headed Giant shares life and poison but keeps energy individual, including undo", () => {
    const c = table("two_headed");
    c.change(1, -7);
    assert.deepEqual(
        c.players.map((p) => p.life),
        [23, 23, 30, 30],
    );
    c.change(0, 14, "poison");
    assert.equal(c.warning(c.players[1]), "");
    c.change(1, 1, "poison");
    assert.match(c.warning(c.players[0]), /15/);
    c.undo();
    assert.deepEqual(
        c.players.map((p) => p.poison),
        [14, 14, 0, 0],
    );
    c.change(0, 3, "energy");
    assert.equal(c.players[1].energy, 0);
});
test("team commander damage reduces shared life but remains separate per recipient", () => {
    const c = table("two_headed_commander");
    c.damage(0, 2, 0, 20);
    c.damage(1, 2, 0, 1);
    assert.deepEqual(
        c.players.map((p) => p.life),
        [39, 39, 60, 60],
    );
    assert.equal(c.warning(c.players[0]), "");
    c.damage(0, 2, 0, 1);
    assert.match(c.warning(c.players[0]), /21/);
    assert.equal(c.warning(c.players[1]), "");
});
test("Archenemy Commander shares only allied life, not poison or damage records", () => {
    const c = table("archenemy");
    c.damage(2, 0, 0, 5);
    assert.deepEqual(
        c.players.map((p) => p.life),
        [60, 55, 55, 55],
    );
    assert.equal(c.players[1].commander[0][0], 0);
    c.change(2, 10, "poison");
    assert.equal(c.players[1].poison, 0);
    assert.match(c.warning(c.players[2]), /10/);
});
test("team draft keeps all six life totals independent", () => {
    const c = table("team_draft");
    c.change(0, -4);
    assert.deepEqual(
        c.players.map((p) => p.life),
        [16, 20, 20, 20, 20, 20],
    );
    assert.match(c.playerRole(3), /Equipe B.*duelo 1/);
});
test("invalid team sizes and unsupported format cannot replace the table", () => {
    const c = counter();
    c.draftFormat = "two_headed";
    c.choosePreset();
    c.draftCount = 3;
    c.pendingStart = true;
    c.start();
    assert.equal(c.format, "standard");
    c.draftFormat = "missing";
    c.start();
    assert.equal(c.format, "standard");
});
test("Brawl and Oathbreaker never apply Commander damage loss", () => {
    for (const format of ["brawl", "brawl_multi", "oathbreaker"]) {
        const c = table(format);
        c.damage(0, 1, 0, 21);
        assert.equal(c.players[0].life, presets[format].life);
        assert.equal(c.warning(c.players[0]), "");
    }
});
test("planar die has one planeswalk, one chaos and four blank faces", () => {
    const c = table("planechase");
    const results = [];
    for (let face = 0; face < 6; face++) {
        c.roll = () => face;
        c.planarDie();
        results.push(c.result);
    }
    assert.equal(results.filter((r) => r.includes("Planeswalk")).length, 1);
    assert.equal(results.filter((r) => r.includes("Caos")).length, 1);
    assert.equal(results.filter((r) => r.includes("em branco")).length, 4);
});
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
