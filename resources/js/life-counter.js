const STORAGE_KEY = 'eldritch-arena:life-counter:v2';

const GAME_OPTIONS = [
  {
    id: 'magic',
    label: 'Magic: The Gathering',
    shortLabel: 'Magic',
    icon: '✦',
    description: 'Construído, Brawl, Gigante de Duas Cabeças, Commander ou configuração personalizada.',
  },
  {
    id: 'yugioh',
    label: 'Yu-Gi-Oh!',
    shortLabel: 'Yu-Gi-Oh!',
    icon: '◈',
    description: 'Duelo TCG com 8.000 LP ou Speed Duel com 4.000 LP.',
  },
];

const FORMAT_OPTIONS = {
  magic: [
    {
      id: 'constructed',
      label: 'Tradicional',
      description: 'Standard, Modern, Pioneer, Pauper, Limitado e partidas casuais.',
      life: 20,
      defaultPlayers: 2,
      minPlayers: 2,
      maxPlayers: 6,
      increments: [1, 5],
      poisonLimit: 10,
    },
    {
      id: 'brawl',
      label: 'Brawl',
      description: 'Duelo 1×1 no estilo Commander.',
      life: 25,
      defaultPlayers: 2,
      minPlayers: 2,
      maxPlayers: 2,
      increments: [1, 5],
      poisonLimit: 10,
    },
    {
      id: 'two-headed-giant',
      label: 'Gigante de Duas Cabeças',
      description: 'Duas equipes com total de vida compartilhado.',
      life: 30,
      defaultPlayers: 2,
      minPlayers: 2,
      maxPlayers: 2,
      increments: [1, 5],
      poisonLimit: 15,
      teams: true,
    },
    {
      id: 'commander',
      label: 'Commander',
      description: 'Multiplayer com veneno e dano individual por comandante.',
      life: 40,
      defaultPlayers: 4,
      minPlayers: 2,
      maxPlayers: 6,
      increments: [1, 5],
      poisonLimit: 10,
      commanderDamage: true,
    },
    {
      id: 'custom',
      label: 'Personalizado',
      description: 'Escolha a vida inicial e a quantidade de jogadores.',
      life: null,
      defaultPlayers: 2,
      minPlayers: 2,
      maxPlayers: 6,
      increments: [1, 5],
      poisonLimit: 10,
    },
  ],
  yugioh: [
    {
      id: 'tcg',
      label: 'Duelo TCG',
      description: 'Formato tradicional oficial para dois duelistas.',
      life: 8000,
      defaultPlayers: 2,
      minPlayers: 2,
      maxPlayers: 2,
      increments: [100, 1000],
    },
    {
      id: 'speed-duel',
      label: 'Speed Duel',
      description: 'Partida rápida com regras e baralhos de Speed Duel.',
      life: 4000,
      defaultPlayers: 2,
      minPlayers: 2,
      maxPlayers: 2,
      increments: [100, 1000],
    },
  ],
};

const PLAYER_COLORS = [
  ['#7c3aed', '#312e81'],
  ['#0891b2', '#164e63'],
  ['#be123c', '#4c0519'],
  ['#15803d', '#052e16'],
  ['#c2410c', '#431407'],
  ['#a21caf', '#4a044e'],
];

const clone = (value) => JSON.parse(JSON.stringify(value));

export default function lifeCounter() {
  return {
    gameOptions: GAME_OPTIONS,
    formatOptions: FORMAT_OPTIONS,
    screen: 'setup',
    game: '',
    formatId: '',
    playerCount: 2,
    customLife: 20,
    players: [],
    undoStack: [],
    savedMatch: null,
    resumeAvailable: false,
    tabletop: true,
    toolsOpen: false,
    resetOpen: false,
    editingLifeId: null,
    lifeEditorValue: 0,
    editingNameId: null,
    expandedPlayerIds: [],
    toolResult: null,
    startedAt: null,
    elapsedSeconds: 0,
    timerId: null,

    init() {
      try {
        const saved = JSON.parse(localStorage.getItem(STORAGE_KEY));

        if (saved?.version === 2 && Array.isArray(saved.players) && saved.players.length >= 2) {
          this.savedMatch = saved;
          this.resumeAvailable = true;
        }
      } catch {
        localStorage.removeItem(STORAGE_KEY);
      }
    },

    selectGame(gameId) {
      this.game = gameId;
      this.selectFormat(this.formatOptions[gameId][0].id);
    },

    selectFormat(formatId) {
      this.formatId = formatId;
      const format = this.currentFormat();
      this.playerCount = format.defaultPlayers;
      this.customLife = format.life ?? 20;
    },

    currentGame() {
      return this.gameOptions.find((game) => game.id === this.game) ?? null;
    },

    currentFormat() {
      return (this.formatOptions[this.game] ?? []).find((format) => format.id === this.formatId) ?? null;
    },

    playerChoices() {
      const format = this.currentFormat();

      if (!format) {
        return [];
      }

      return Array.from(
        { length: format.maxPlayers - format.minPlayers + 1 },
        (_, index) => format.minPlayers + index,
      );
    },

    initialLife() {
      const format = this.currentFormat();
      const selectedLife = format?.life ?? Number(this.customLife);
      return Math.max(1, Math.min(999999, Number(selectedLife) || 20));
    },

    startMatch() {
      const format = this.currentFormat();

      if (!format) {
        return;
      }

      const count = Math.max(format.minPlayers, Math.min(format.maxPlayers, Number(this.playerCount)));
      const life = this.initialLife();

      this.playerCount = count;
      this.players = Array.from({ length: count }, (_, index) => ({
        id: index + 1,
        name: format.teams ? `Equipe ${index + 1}` : `Jogador ${index + 1}`,
        life,
        poison: 0,
        commanderDamage: {},
      }));

      if (format.commanderDamage) {
        this.players.forEach((player) => {
          this.players.forEach((source) => {
            if (player.id !== source.id) {
              player.commanderDamage[source.id] = 0;
            }
          });
        });
      }

      this.screen = 'match';
      this.undoStack = [];
      this.expandedPlayerIds = [];
      this.toolsOpen = false;
      this.startedAt = Date.now();
      this.startTimer();
      this.save();
    },

    resumeMatch() {
      if (!this.savedMatch) {
        return;
      }

      this.game = this.savedMatch.game;
      this.formatId = this.savedMatch.formatId;
      this.playerCount = this.savedMatch.playerCount;
      this.customLife = this.savedMatch.customLife;
      this.players = clone(this.savedMatch.players);
      this.tabletop = this.savedMatch.tabletop ?? true;
      this.startedAt = this.savedMatch.startedAt ?? Date.now();
      this.screen = 'match';
      this.undoStack = [];
      this.startTimer();
    },

    snapshot() {
      return {
        players: clone(this.players),
        startedAt: this.startedAt,
      };
    },

    checkpoint() {
      this.undoStack.push(this.snapshot());

      if (this.undoStack.length > 30) {
        this.undoStack.shift();
      }
    },

    undoLastChange() {
      const previous = this.undoStack.pop();

      if (!previous) {
        return;
      }

      const restoredPlayers = clone(previous.players);

      this.players.forEach((player, index) => {
        Object.assign(player, restoredPlayers[index]);
      });
      this.startedAt = previous.startedAt;
      this.save();
    },

    findPlayer(playerId) {
      return this.players.find((player) => player.id === playerId);
    },

    changeLife(playerId, amount) {
      const player = this.findPlayer(playerId);

      if (!player) {
        return;
      }

      this.checkpoint();
      const nextLife = Number(player.life) + Number(amount);
      player.life = this.game === 'yugioh'
        ? Math.max(0, Math.min(999999, nextLife))
        : Math.max(-9999, Math.min(999999, nextLife));
      this.save();
    },

    beginLifeEdit(playerId) {
      const player = this.findPlayer(playerId);

      if (!player) {
        return;
      }

      this.editingLifeId = playerId;
      this.lifeEditorValue = player.life;
    },

    applyLifeEdit(playerId) {
      const player = this.findPlayer(playerId);
      const parsedLife = Number(this.lifeEditorValue);

      if (!player || !Number.isFinite(parsedLife)) {
        return;
      }

      this.checkpoint();
      player.life = this.game === 'yugioh'
        ? Math.max(0, Math.min(999999, Math.round(parsedLife)))
        : Math.max(-9999, Math.min(999999, Math.round(parsedLife)));
      this.editingLifeId = null;
      this.save();
    },

    beginNameEdit(playerId) {
      this.editingNameId = playerId;
    },

    applyNameEdit(playerId) {
      const player = this.findPlayer(playerId);

      if (player) {
        player.name = player.name.trim().slice(0, 24) || `Jogador ${playerId}`;
      }

      this.editingNameId = null;
      this.save();
    },

    changePoison(playerId, amount) {
      const player = this.findPlayer(playerId);

      if (!player) {
        return;
      }

      this.checkpoint();
      player.poison = Math.max(0, Math.min(99, Number(player.poison) + Number(amount)));
      this.save();
    },

    changeCommanderDamage(playerId, sourceId, amount) {
      const player = this.findPlayer(playerId);

      if (!player) {
        return;
      }

      this.checkpoint();
      const current = Number(player.commanderDamage[sourceId] ?? 0);
      player.commanderDamage[sourceId] = Math.max(0, Math.min(99, current + Number(amount)));
      this.save();
    },

    quickAdjustments() {
      const increments = this.currentFormat()?.increments ?? [1, 5];
      return [-increments[1], -increments[0], increments[0], increments[1]];
    },

    signed(number) {
      return Number(number) > 0 ? `+${number}` : `${number}`;
    },

    toggleDetails(playerId) {
      this.expandedPlayerIds = this.expandedPlayerIds.includes(playerId)
        ? this.expandedPlayerIds.filter((id) => id !== playerId)
        : [...this.expandedPlayerIds, playerId];
    },

    detailsExpanded(playerId) {
      return this.expandedPlayerIds.includes(playerId);
    },

    commanderMaximum(player) {
      return Math.max(0, ...Object.values(player.commanderDamage ?? {}).map(Number));
    },

    isDefeated(player) {
      const format = this.currentFormat();
      return player.life <= 0
        || (format?.poisonLimit && player.poison >= format.poisonLimit)
        || (format?.commanderDamage && this.commanderMaximum(player) >= 21);
    },

    lossReason(player) {
      const format = this.currentFormat();

      if (format?.commanderDamage && this.commanderMaximum(player) >= 21) {
        return '21 de dano de comandante';
      }

      if (format?.poisonLimit && player.poison >= format.poisonLimit) {
        return `${format.poisonLimit} marcadores de veneno`;
      }

      return 'sem pontos de vida';
    },

    playerStyle(index) {
      const colors = PLAYER_COLORS[index % PLAYER_COLORS.length];
      return `background: radial-gradient(circle at top left, ${colors[0]}55, transparent 55%), linear-gradient(145deg, ${colors[1]}ee, #070411 78%);`;
    },

    resetMatch() {
      this.checkpoint();
      const life = this.initialLife();

      this.players.forEach((player) => {
        player.life = life;
        player.poison = 0;
        Object.keys(player.commanderDamage ?? {}).forEach((sourceId) => {
          player.commanderDamage[sourceId] = 0;
        });
      });

      this.startedAt = Date.now();
      this.elapsedSeconds = 0;
      this.resetOpen = false;
      this.save();
    },

    newMatch() {
      this.save();
      this.savedMatch = JSON.parse(localStorage.getItem(STORAGE_KEY));
      this.resumeAvailable = true;
      this.screen = 'setup';
      this.toolsOpen = false;
      this.resetOpen = false;
      this.stopTimer();
    },

    rollDie(sides) {
      this.toolResult = {
        title: `Resultado do d${sides}`,
        value: Math.floor(Math.random() * sides) + 1,
      };
    },

    flipCoin() {
      this.toolResult = {
        title: 'Cara ou coroa',
        value: Math.random() < 0.5 ? 'Cara' : 'Coroa',
      };
    },

    randomPlayer() {
      const selected = this.players[Math.floor(Math.random() * this.players.length)];
      this.toolResult = {
        title: 'Jogador inicial',
        value: selected?.name ?? '—',
      };
    },

    async toggleFullscreen() {
      try {
        if (document.fullscreenElement) {
          await document.exitFullscreen();
        } else {
          await document.documentElement.requestFullscreen();
        }
      } catch {
        this.toolResult = {
          title: 'Tela cheia',
          value: 'Não disponível neste navegador',
        };
        this.toolsOpen = true;
      }
    },

    startTimer() {
      this.stopTimer();
      this.updateElapsed();
      this.timerId = window.setInterval(() => this.updateElapsed(), 1000);
    },

    stopTimer() {
      if (this.timerId) {
        window.clearInterval(this.timerId);
        this.timerId = null;
      }
    },

    updateElapsed() {
      this.elapsedSeconds = Math.max(0, Math.floor((Date.now() - this.startedAt) / 1000));
    },

    elapsed() {
      const hours = Math.floor(this.elapsedSeconds / 3600);
      const minutes = Math.floor((this.elapsedSeconds % 3600) / 60);
      const seconds = this.elapsedSeconds % 60;
      const values = hours > 0 ? [hours, minutes, seconds] : [minutes, seconds];
      return values.map((value) => String(value).padStart(2, '0')).join(':');
    },

    save() {
      if (!this.players.length) {
        return;
      }

      const state = {
        version: 2,
        game: this.game,
        formatId: this.formatId,
        playerCount: this.playerCount,
        customLife: this.customLife,
        players: clone(this.players),
        tabletop: this.tabletop,
        startedAt: this.startedAt,
      };

      localStorage.setItem(STORAGE_KEY, JSON.stringify(state));
      this.savedMatch = state;
      this.resumeAvailable = true;
    },
  };
}
