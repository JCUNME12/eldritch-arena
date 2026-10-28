import { test, beforeEach } from "node:test";
import assert from "node:assert/strict";
import counter, { newPlayers, presets } from "../resources/js/life-counter.js";
let saved;
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
test("editing the current Yu-Gi-Oh table preserves its chosen format and points", () => {
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
    return c;
}
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

test("opening the page never restores an old table or reads browser storage", () => {
    globalThis.localStorage.getItem = () => { throw Error("must not read old table"); };
    const c = counter(); c.init();
    assert.equal(c.hasStarted, false);
    assert.equal(c.settings, true);
    assert.equal(c.setupStep, "game");
    c.chooseGame("ygo"); c.start();
    assert.equal(c.hasStarted, true);
    assert.equal(c.players[0].life, 8000);
    c.change(0, -500);
    const reopened = counter(); reopened.init();
    assert.equal(reopened.hasStarted, false);
    assert.equal(reopened.settings, true);
    assert.equal(reopened.setupStep, "game");
});
test("all presets start immediately on an empty table and reset correctly", () => {
    for (const format of Object.keys(presets)) {
        const c = table(format);
        assert.equal(c.hasStarted, true); assert.equal(c.settings, false);
        assert.equal(c.format, format);
        c.change(0, -3); c.reset(); c.reset();
        assert.equal(c.players[0].life, presets[format].leaderLife || presets[format].life);
    }
});
test("replacing an active table still needs confirmation and clears old tools", () => {
    const c = table("standard"); c.change(0, -3); c.openSettings();
    c.chooseGame("ygo"); c.start();
    assert.equal(c.pendingStart, true); assert.equal(c.players[0].life, 17);
    c.tools=true; c.historyOpen=true; c.start();
    assert.equal(c.players[0].life, 8000); assert.equal(c.pendingStart, false);
    assert.equal(c.tools, false); assert.equal(c.historyOpen, false);
});
test("dice dialog opens empty, rolls, closes and returns focus", () => {
    const c = table("standard"); let focused=false;
    c.$nextTick=fn=>fn(); c.$refs={toolsButton:{focus:()=>focused=true}};
    c.result="old roll"; c.openTools();
    assert.equal(c.tools,true); assert.equal(c.result,"");
    c.roll=()=>5; c.dice(6); assert.equal(c.result,"D6: 6");
    c.closeTools(); assert.equal(c.tools,false); assert.equal(focused,true);
});
test("life changes work without storage and undo stays available in the current session", () => {
    globalThis.localStorage.setItem=()=>{throw Error("disabled");};
    const c=table("standard"); c.change(0,-25);
    assert.equal(c.players[0].life,-5); c.undo(); assert.equal(c.players[0].life,20);
});
