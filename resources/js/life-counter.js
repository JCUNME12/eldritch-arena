export const presets = {
    standard: {
        name: "Magic · Standard / Construído",
        life: 20,
        count: 2,
        step: 1,
        magic: true,
    },
    commander: {
        name: "Magic · Commander",
        life: 40,
        count: 4,
        step: 1,
        magic: true,
    },
    ygo: { name: "Yu-Gi-Oh! · TCG", life: 8000, count: 2, step: 100 },
    speed: { name: "Yu-Gi-Oh! · Speed Duel", life: 4000, count: 2, step: 100 },
    custom: {
        name: "Mesa personalizada",
        life: 20,
        count: 2,
        step: 1,
        magic: true,
    },
};
const key = "eldritch.table.v2";
export const colors = [
    "#265754",
    "#513c69",
    "#6d3d43",
    "#365879",
    "#6d592f",
    "#434c67",
];
export function newPlayers(count, life) {
    return Array.from({ length: count }, (_, i) => ({
        id: i,
        name: `Jogador ${i + 1}`,
        life,
        color: colors[i],
        poison: 0,
        energy: 0,
        experience: 0,
        commander: Array.from({ length: count }, () => [0, 0]),
        rotated: false,
    }));
}
export default function lifeCounter() {
    return {
        presets,
        colors,
        format: "standard",
        draftFormat: "standard",
        draftCount: 2,
        draftLife: 20,
        players: newPlayers(2, 20),
        initial: 20,
        step: 1,
        pendingStart: false,
        pendingReset: false,
        settings: false,
        tools: false,
        historyOpen: false,
        active: null,
        history: [],
        result: "",
        customAmount: 100,
        storageError: false,
        init() {
            try {
                const s = JSON.parse(localStorage.getItem(key));
                if (
                    s &&
                    Object.hasOwn(presets, s.format) &&
                    Number.isInteger(s.initial) &&
                    s.initial > 0 &&
                    s.initial <= 99999 &&
                    Array.isArray(s.players) &&
                    s.players.length >= 1 &&
                    s.players.length <= 6 &&
                    s.players.every(
                        (p, i) =>
                            p.id === i &&
                            typeof p.rotated === "boolean" &&
                            typeof p.name === "string" &&
                            p.name.length <= 30 &&
                            Number.isInteger(p.life) &&
                            Math.abs(p.life) <= 999999 &&
                            colors.includes(p.color) &&
                            ["poison", "energy", "experience"].every(
                                (k) => Number.isInteger(p[k]) && p[k] >= 0,
                            ) &&
                            Array.isArray(p.commander) &&
                            p.commander.length === s.players.length &&
                            p.commander.every(
                                (a) =>
                                    Array.isArray(a) &&
                                    a.length === 2 &&
                                    a.every(
                                        (n) => Number.isInteger(n) && n >= 0,
                                    ),
                            ),
                    )
                ) {
                    this.format = s.format;
                    this.initial = s.initial;
                    this.players = s.players;
                    this.step = presets[s.format].step;
                }
            } catch {
                this.storageError = true;
            }
        },
        trapFocus(event) {
            const dialog = event.target.closest('[role="dialog"]');
            const controls = [
                ...dialog.querySelectorAll(
                    "button,input,select,textarea,a[href]",
                ),
            ].filter((el) => !el.disabled && el.offsetParent !== null);
            if (!controls.length) return;
            const first = controls[0],
                last = controls[controls.length - 1];
            if (event.shiftKey && document.activeElement === first) {
                event.preventDefault();
                last.focus();
            } else if (!event.shiftKey && document.activeElement === last) {
                event.preventDefault();
                first.focus();
            }
        },
        save() {
            try {
                localStorage.setItem(
                    key,
                    JSON.stringify({
                        format: this.format,
                        initial: this.initial,
                        players: this.players,
                    }),
                );
            } catch {
                this.storageError = true;
            }
        },
        checkpoint(label) {
            this.history.unshift({
                label,
                players: JSON.parse(JSON.stringify(this.players)),
            });
            this.history = this.history.slice(0, 60);
        },
        change(i, delta, field = "life") {
            delta = Number(delta);
            if (
                !Number.isInteger(delta) ||
                Math.abs(delta) > 999999 ||
                !this.players[i]
            )
                return;
            const p = this.players[i];
            this.checkpoint(
                `${p.name}: ${field === "life" ? "vida" : field} ${delta > 0 ? "+" : ""}${delta}`,
            );
            p[field] = Math.max(
                field === "life" ? -999999 : 0,
                Math.min(999999, p[field] + delta),
            );
            this.save();
        },
        damage(target, source, slot, delta) {
            const p = this.players[target];
            const before = p.commander[source][slot];
            const after = Math.max(0, Math.min(999, before + delta));
            if (before === after) return;
            this.checkpoint(
                `${p.name}: dano de ${this.players[source].name}, comandante ${slot + 1}`,
            );
            p.commander[source][slot] = after;
            p.life = Math.max(
                -999999,
                Math.min(999999, p.life - (after - before)),
            );
            this.save();
        },
        undo() {
            const state = this.history.shift();
            if (state) {
                this.players = state.players;
                this.save();
            }
        },
        openSettings() {
            this.draftFormat = this.format;
            this.draftCount = this.players.length;
            this.draftLife = this.initial;
            this.pendingStart = false;
            this.settings = true;
        },
        choosePreset() {
            const p = presets[this.draftFormat];
            this.draftCount = p.count;
            this.draftLife = p.life;
        },
        start() {
            const count = Number(this.draftCount),
                life = Number(this.draftLife);
            if (
                !Number.isInteger(count) ||
                count < 1 ||
                count > 6 ||
                !Number.isInteger(life) ||
                life < 1 ||
                life > 99999
            )
                return;
            if (!this.pendingStart) {
                this.pendingStart = true;
                return;
            }
            this.format = this.draftFormat;
            this.initial = life;
            this.step = presets[this.format].step;
            this.players = newPlayers(count, life);
            this.history = [];
            this.result = "";
            this.settings = false;
            this.active = null;
            this.save();
        },
        reset() {
            if (!this.pendingReset) {
                this.pendingReset = true;
                return;
            }
            this.pendingReset = false;
            this.checkpoint("Reiniciar mesa");
            this.players = this.players.map((p) => ({
                ...newPlayers(this.players.length, this.initial)[p.id],
                name: p.name,
                color: p.color,
                rotated: p.rotated,
            }));
            this.save();
        },
        roll(sides) {
            const n = new Uint32Array(1);
            const limit = Math.floor(4294967296 / sides) * sides;
            do {
                crypto.getRandomValues(n);
            } while (n[0] >= limit);
            return n[0] % sides;
        },
        dice(sides) {
            this.result = `D${sides}: ${this.roll(sides) + 1}`;
        },
        coin() {
            this.result = this.roll(2) ? "Cara" : "Coroa";
        },
        randomPlayer() {
            this.result = `Começa: ${this.players[this.roll(this.players.length)].name}`;
        },
        warning(p) {
            if (
                this.format === "commander" &&
                p.commander.some((a) => a.some((n) => n >= 21))
            )
                return "21+ de dano de um comandante";
            if (presets[this.format].magic && p.poison >= 10)
                return "10+ marcadores de veneno";
            if (p.life <= 0) return "Vida em zero ou abaixo";
            return "";
        },
        async fullscreen() {
            try {
                if (document.fullscreenElement) await document.exitFullscreen();
                else await this.$root.requestFullscreen();
            } catch {
                this.result = "Tela cheia não está disponível neste navegador.";
                this.tools = true;
            }
        },
    };
}
