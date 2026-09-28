# Formatos de Magic no marcador

Atualização de 28/09/2026. Revisão local; publicação na AWS pendente da revisão do usuário.

O marcador oferece 21 configurações de Magic, além de Yu-Gi-Oh! e mesa personalizada. A escolha é feita primeiro por jogo e depois por formato. Os formatos também estão disponíveis para identificar torneios. Isto não implementa um simulador de cartas, organizador automático de drafts ou validação de decks/listas de banidas. Archenemy agora possui um baralho virtual de esquemas; veja ESQUEMAS_ARCHENEMY.md.

| Configuração | Pontos e comportamento |
| --- | --- |
| Standard, Modern, Pioneer, Legacy, Vintage, Pauper | 20 individuais; descrição da diferença de construção |
| Commander | 40 individuais; dano de combate por comandante e destinatário |
| Brawl duelo / multiplayer | 25 / 30; sem derrota por dano de comandante |
| Booster Draft, Selado, Pick-Two Draft | Duelo de 20; seleção de cartas fora do sistema |
| Gigante de Duas Cabeças e seu Draft | 4 jogadores, 30 por equipe; veneno compartilhado, alerta em 15 |
| Commander Gigante de Duas Cabeças | 60 por equipe; dano de comandante individual |
| Planechase / Planechase Commander | 20 / 40; dado planar com 1 planeswalk, 1 caos e 4 faces vazias |
| Archenemy | Regra Commander: 60 para o arqui-inimigo e 60 compartilhados pelos aliados; veneno e dano de comandante individuais |
| Oathbreaker | 20 individuais, sem regra de dano de comandante |
| Conspiracy | 20 individuais; draft e conspirações resolvidos na mesa |
| Booster Draft por Equipes | 6 jogadores, dois times de três; três duelos com vida individual |

Os parceiros têm painéis separados para nomes, energia e outros marcadores individuais. Quando a vida é compartilhada, os dois painéis mostram o mesmo total da equipe: não devem ser somados. Alterações e desfazer sincronizam os parceiros. Contagens de jogadores são limitadas nos presets de equipes. Archenemy começa sempre pelo arqui-inimigo; 2HG sorteia a equipe inicial.

Pick-Two significa duas cartas por escolha, não uma equipe de dois. A alternativa Draft Gigante de Duas Cabeças cobre o draft em duplas. Efeitos dos planos e esquemas, custos do dado planar, monarca e eliminações são resolvidos pelos jogadores. O baralho virtual revela e mantém os esquemas contínuos. Alertas de vida, veneno e comandante são lembretes; não eliminam automaticamente jogadores.

A chave de armazenamento anterior (`eldritch.table.v2`) é preservada. Mesas antigas válidas continuam restaurando. Valores personalizados são identificados na tela. A adaptação de mesas antigas de Archenemy para o preset unificado está documentada em ESQUEMAS_ARCHENEMY.md.

## Referências oficiais

- https://magic.wizards.com/en/formats/two-headed-giant
- https://magic.wizards.com/en/formats/oathbreaker
- https://magic.wizards.com/en/news/announcements/evolving-archenemy
- https://magic.wizards.com/en/news/feature/duskmourn-house-of-horror-release-notes
- https://media.wizards.com/2025/downloads/MagicCompRules%2020250404.pdf (904.13: Archenemy Commander)
- https://media.wizards.com/2025/downloads/MagicCompRules%2020250919.pdf (903.12: Brawl)
- https://magic.wizards.com/en/news/announcements/introducing-pick-two-draft
- https://magic.wizards.com/en/news/feature/what-is-planechase-and-why-is-it-awesome

## Validação

19 testes JavaScript: configurações, restauração, reinício, desfazer, vida e veneno de equipe, energia individual, dano de comandante, Archenemy, dado planar e entrada inválida. Build de produção e compilação Blade verificados. Suíte Laravel: 17 testes e 125 asserções em SQLite e PostgreSQL, incluindo aceitação dos novos formatos e rejeição quando o jogo não corresponde. Revisão no navegador em desktop e largura de 390 pixels: seleção de formatos, vida compartilhada, desfazer e dado planar; sem erros JavaScript observados. Nenhuma mudança de schema ou migration é necessária.
