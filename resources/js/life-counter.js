export const presets = {
    standard: {
        name: "Magic · Standard",
        life: 20,
        count: 2,
        step: 1,
        magic: true,
        description: "Construído com rotação de coleções. Vida individual: 20.",
    },
    modern: {
        name: "Magic · Modern",
        life: 20,
        count: 2,
        step: 1,
        magic: true,
        description:
            "Construído sem rotação; possui sua própria lista de cartas legais. Vida individual: 20.",
    },
    pioneer: {
        name: "Magic · Pioneer",
        life: 20,
        count: 2,
        step: 1,
        magic: true,
        description:
            "Construído sem rotação, com pool diferente de Modern. Vida individual: 20.",
    },
    legacy: {
        name: "Magic · Legacy",
        life: 20,
        count: 2,
        step: 1,
        magic: true,
        description:
            "Pool histórico com lista de cartas banidas. Vida individual: 20.",
    },
    vintage: {
        name: "Magic · Vintage",
        life: 20,
        count: 2,
        step: 1,
        magic: true,
        description:
            "Pool histórico com cartas banidas e restritas. Vida individual: 20.",
    },
    pauper: {
        name: "Magic · Pauper",
        life: 20,
        count: 2,
        step: 1,
        magic: true,
        description:
            "Decks de cartas com impressão comum válida no formato. Vida individual: 20.",
    },
    commander: {
        name: "Magic · Commander (EDH)",
        life: 40,
        count: 4,
        step: 1,
        magic: true,
        description:
            "40 por jogador. Dano de combate de cada comandante é contado separadamente.",
        commanderDamage: true,
    },
    brawl: {
        name: "Magic · Brawl · duelo",
        life: 25,
        count: 2,
        step: 1,
        magic: true,
        description:
            "25 por jogador no duelo. Não utiliza derrota por dano de comandante.",
        fixedCount: 2,
    },
    brawl_multi: {
        name: "Magic · Brawl · multiplayer",
        life: 30,
        count: 4,
        step: 1,
        magic: true,
        description:
            "30 por jogador em mesa multiplayer. Não utiliza dano de comandante.",
        minCount: 3,
    },
    draft: {
        name: "Magic · Booster Draft",
        life: 20,
        count: 2,
        step: 1,
        magic: true,
        description:
            "20 por jogador em cada duelo. A seleção de cartas acontece fora do marcador.",
        fixedCount: 2,
    },
    sealed: {
        name: "Magic · Selado",
        life: 20,
        count: 2,
        step: 1,
        magic: true,
        description:
            "20 por jogador em cada duelo. A seleção de cartas acontece fora do marcador.",
        fixedCount: 2,
    },
    pick_two: {
        name: "Magic · Pick-Two Draft (duas escolhas)",
        life: 20,
        count: 2,
        step: 1,
        magic: true,
        description:
            "20 por jogador em cada duelo. Escolhem-se duas cartas por escolha; não significa equipes de dois. A seleção de cartas acontece fora do marcador.",
        fixedCount: 2,
    },
    two_headed: {
        name: "Magic · Gigante de Duas Cabeças",
        life: 30,
        count: 4,
        step: 1,
        magic: true,
        description:
            "2 contra 2. Cada equipe compartilha 30 de vida e veneno (limite 15). Energia e experiência são individuais.",
        layout: "two_headed",
        fixedCount: 4,
    },
    two_headed_draft: {
        name: "Magic · Draft · Gigante de Duas Cabeças",
        life: 30,
        count: 4,
        step: 1,
        magic: true,
        description:
            "Draft para equipes de dois. 30 de vida por equipe; veneno compartilhado (limite 15). A seleção de cartas acontece fora do marcador.",
        layout: "two_headed",
        fixedCount: 4,
    },
    two_headed_commander: {
        name: "Magic · Commander · Gigante de Duas Cabeças",
        life: 60,
        count: 4,
        step: 1,
        magic: true,
        description:
            "60 de vida por equipe, veneno compartilhado (limite 15). Dano de comandante é individual.",
        layout: "two_headed",
        fixedCount: 4,
        commanderDamage: true,
    },
    planechase: {
        name: "Magic · Planechase",
        life: 20,
        count: 4,
        step: 1,
        magic: true,
        description:
            "20 por jogador e dado planar. Use seu baralho de planos na mesa; efeitos e custos são resolvidos pelos jogadores.",
        planar: true,
    },
    planechase_commander: {
        name: "Magic · Planechase · Commander",
        life: 40,
        count: 4,
        step: 1,
        magic: true,
        description:
            "Commander com 40 por jogador e dado planar. Planos e efeitos são resolvidos na mesa.",
        planar: true,
        commanderDamage: true,
    },
    archenemy: {
        name: "Magic · Archenemy",
        life: 60,
        count: 4,
        step: 1,
        magic: true,
        description:
            "Regras de Archenemy Commander: arqui-inimigo com 60 de vida e primeiro turno; aliados compartilham 60. Veneno e dano de comandante são individuais. Prepare o baralho de esquemas abaixo.",
        layout: "archenemy_shared",
        minCount: 4,
        commanderDamage: true,
    },
    oathbreaker: {
        name: "Magic · Oathbreaker",
        life: 20,
        count: 4,
        step: 1,
        magic: true,
        description:
            "20 por jogador; planeswalker e feitiço assinatura. Sem derrota por dano de comandante.",
        minCount: 3,
        maxCount: 5,
    },
    conspiracy: {
        name: "Magic · Conspiracy",
        life: 20,
        count: 4,
        step: 1,
        magic: true,
        description:
            "Draft seguido de multiplayer, com 20 por jogador. Conspirações e seleção de cartas são resolvidas na mesa.",
        minCount: 3,
        maxCount: 5,
    },
    team_draft: {
        name: "Magic · Booster Draft por Equipes",
        life: 20,
        count: 6,
        step: 1,
        magic: true,
        description:
            "Equipes de três; duelos individuais 1×4, 2×5 e 3×6. Cada jogador tem 20 de vida, sem compartilhar. Draft e resultados são geridos na mesa.",
        layout: "team_draft",
        fixedCount: 6,
    },
    ygo: {
        name: "Yu-Gi-Oh! · TCG",
        life: 8000,
        count: 2,
        step: 100,
    },
    speed: {
        name: "Yu-Gi-Oh! · Speed Duel",
        life: 4000,
        count: 2,
        step: 100,
    },
    custom: {
        name: "Mesa personalizada",
        life: 20,
        count: 2,
        step: 1,
        magic: true,
        description:
            "Ajuste os pontos e a quantidade de jogadores conforme o acordo da mesa.",
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
        draftGame: "magic",
        setupStep: "game",
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
        migrationNotice: "",
        init() {
            try {
                const s = JSON.parse(localStorage.getItem(key));
                const oldClassic =
                    s?.format === "archenemy" && s.rulesVersion !== 3;
                if (s?.format === "archenemy_commander") s.format = "archenemy";
                if (
                    s &&
                    Object.hasOwn(presets, s.format) &&
                    Number.isInteger(s.initial) &&
                    s.initial > 0 &&
                    s.initial <= 99999 &&
                    Array.isArray(s.players) &&
                    s.players.length >= 1 &&
                    s.players.length <= 6 &&
                    this.validCount(s.format, s.players.length) &&
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
                    if (oldClassic) {
                        const teamLife = Math.max(
                            -999999,
                            Math.min(
                                999999,
                                s.players
                                    .slice(1)
                                    .reduce((total, p) => total + p.life, 0),
                            ),
                        );
                        s.players.slice(1).forEach((p) => (p.life = teamLife));
                        s.initial = 60;
                        this.migrationNotice =
                            "Mesa antiga adaptada: a vida restante dos aliados foi somada em um total compartilhado. A vida atual do arqui-inimigo foi preservada; ao reiniciar, ambos os lados começam com 60.";
                    }
                    this.format = s.format;
                    this.initial = s.initial;
                    this.players = s.players;
                    this.step = presets[s.format].step;
                    this.players.forEach((p) => {
                        this.sync(p.id, "life");
                        this.sync(p.id, "poison");
                    });
                }
            } catch {
                this.storageError = true;
            }
        },
        validCount(format, count) {
            const p = presets[format];
            return (
                !!p &&
                (p.fixedCount
                    ? count === p.fixedCount
                    : count >= (p.minCount || 1) && count <= (p.maxCount || 6))
            );
        },
        availableCounts() {
            return [1, 2, 3, 4, 5, 6].filter((n) =>
                this.validCount(this.draftFormat, n),
            );
        },
        group(i) {
            const layout = presets[this.format].layout;
            if (layout === "two_headed") return Math.floor(i / 2);
            if (layout === "archenemy_shared") return i === 0 ? 0 : 1;
            return i;
        },
        sync(i, field) {
            const layout = presets[this.format].layout;
            if (
                !(
                    layout === "two_headed" &&
                    ["life", "poison"].includes(field)
                ) &&
                !(layout === "archenemy_shared" && field === "life")
            )
                return;
            const value = this.players[i][field];
            this.players.forEach((p, j) => {
                if (this.group(i) === this.group(j)) p[field] = value;
            });
        },
        playerRole(i) {
            const layout = presets[this.format].layout;
            if (layout === "two_headed")
                return `Equipe ${i < 2 ? "A" : "B"} · vida e veneno compartilhados`;
            if (layout === "team_draft")
                return `Equipe ${i < 3 ? "A" : "B"} · duelo ${(i % 3) + 1} · vida individual`;
            if (layout?.startsWith("archenemy"))
                return i === 0
                    ? "Arqui-inimigo · começa a partida"
                    : layout === "archenemy_shared"
                      ? "Aliados · vida compartilhada"
                      : "Aliado · vida individual";
            return "Vida individual";
        },
        freshPlayers(count, life) {
            const players = newPlayers(count, life);
            if (presets[this.format].leaderLife)
                players[0].life = presets[this.format].leaderLife;
            return players;
        },
        planarDie() {
            const face = this.roll(6);
            this.result =
                face === 0
                    ? "Dado planar: Planeswalk — mudar de plano"
                    : face === 1
                      ? "Dado planar: Caos — resolva a habilidade do plano"
                      : "Dado planar: face em branco";
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
                        rulesVersion: 3,
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
        updatePlayer(index, field, value) {
            const player = this.players[index];
            if (!player || !["name", "color", "rotated"].includes(field))
                return;
            if (field === "name")
                value =
                    String(value).trim().slice(0, 30) || `Jogador ${index + 1}`;
            if (field === "color" && !colors.includes(value)) return;
            if (field === "rotated") value = Boolean(value);
            if (player[field] === value) return;
            this.checkpoint(`Personalizar ${player.name}`);
            player[field] = value;
            this.save();
        },
        change(i, delta, field = "life") {
            delta = Number(delta);
            if (
                !Number.isInteger(delta) ||
                Math.abs(delta) > 999999 ||
                !this.players[i] ||
                !["life", "poison", "energy", "experience"].includes(field)
            )
                return;
            const p = this.players[i];
            this.checkpoint(
                `${p.name}: ${{ life: "vida", poison: "veneno", energy: "energia", experience: "experiência" }[field]} ${delta > 0 ? "+" : ""}${delta}`,
            );
            p[field] = Math.max(
                field === "life" ? -999999 : 0,
                Math.min(999999, p[field] + delta),
            );
            this.sync(i, field);
            this.save();
        },
        damage(target, source, slot, delta) {
            if (
                !presets[this.format].commanderDamage ||
                !this.players[target] ||
                !this.players[source] ||
                ![0, 1].includes(slot) ||
                !Number.isInteger(delta)
            )
                return;
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
            this.sync(target, "life");
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
            this.draftGame =
                this.format === "custom"
                    ? "custom"
                    : ["ygo", "speed"].includes(this.format)
                      ? "ygo"
                      : "magic";
            this.setupStep = "game";
            this.draftCount = this.players.length;
            this.draftLife = this.initial;
            this.pendingStart = false;
            this.settings = true;
        },
        chooseGame(game) {
            if (!["magic", "ygo", "custom"].includes(game)) return;
            if (game !== this.draftGame) {
                this.draftFormat = {
                    magic: "standard",
                    ygo: "ygo",
                    custom: "custom",
                }[game];
                this.choosePreset();
            }
            this.draftGame = game;
            this.pendingStart = false;
            this.setupStep = "format";
        },
        availablePresets() {
            return Object.fromEntries(
                Object.entries(presets).filter(([id]) =>
                    this.draftGame === "custom"
                        ? id === "custom"
                        : this.draftGame === "ygo"
                          ? ["ygo", "speed"].includes(id)
                          : !["ygo", "speed", "custom"].includes(id),
                ),
            );
        },
        choosePreset() {
            const p = presets[this.draftFormat];
            this.pendingStart = false;
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
                !this.validCount(this.draftFormat, count) ||
                !Number.isInteger(life) ||
                life < 1 ||
                life > 99999
            )
                return;
            if (!this.pendingStart) {
                this.pendingStart = true;
                return;
            }
            this.migrationNotice = "";
            this.format = this.draftFormat;
            this.initial = life;
            this.step = presets[this.format].step;
            this.players = this.freshPlayers(count, life);
            this.history = [];
            this.pendingReset = false;
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
                ...this.freshPlayers(this.players.length, this.initial)[p.id],
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
            const layout = presets[this.format].layout;
            if (layout?.startsWith("archenemy"))
                this.result = `Começa: ${this.players[0].name} (arqui-inimigo)`;
            else if (layout === "two_headed")
                this.result = `Começa: equipe ${this.roll(2) ? "B" : "A"}`;
            else if (layout === "team_draft")
                this.result =
                    "Sorteiem o início de cada duelo separadamente usando a moeda.";
            else
                this.result = `Começa: ${this.players[this.roll(this.players.length)].name}`;
        },
        warning(p) {
            if (
                presets[this.format].commanderDamage &&
                p.commander.some((a) => a.some((n) => n >= 21))
            )
                return "21+ de dano de um comandante";
            const poisonLimit =
                presets[this.format].layout === "two_headed" ? 15 : 10;
            if (presets[this.format].magic && p.poison >= poisonLimit)
                return `${poisonLimit}+ marcadores de veneno`;
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
