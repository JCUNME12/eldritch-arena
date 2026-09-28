# Archenemy e baralho de esquemas

## Regra escolhida

O usuário escolheu explicitamente Archenemy Commander, exibido simplesmente como **Archenemy**. Padrão: arqui-inimigo com 60 de vida e primeiro turno; aliados compartilham 60 de vida. Veneno (10) e dano de combate de cada comandante (21) são individuais. Alterações de vida dos aliados sincronizam os painéis.

O preset antigo `archenemy_commander` é restaurado com o nome unificado. Mesas antigas do preset clássico somam a vida restante dos aliados em um total compartilhado, preservam a vida atual do arqui-inimigo e os marcadores individuais. A tela explica essa adaptação. Reiniciar aplica 60 aos dois lados. Eventos antigos não têm seus registros históricos reescritos; novas escolhas de formato usam apenas Archenemy.

## Conteúdo

Catálogo consultado em 28/09/2026: 102 esquemas distintos, identificados pelo oracle_id do Scryfall. Coleções: Archenemy Schemes (45), Nicol Bolas (20), Duskmourn Commander (40). Há sobreposição de cartas entre coleções. Todos os esquemas inclui também as cartas promocionais.

Nove baralhos temáticos provenientes das listas MTGJSON: Assemble the Doomsday Machine, Bring About the Undead Apocalypse, Scorch the World with Dragonfire, Trample Civilization Underfoot, Nicol Bolas, Death Toll, Endless Punishment, Jump Scare! e Miracle Worker. São usados nomes distintos sem as repetições dos produtos antigos, para seguir o formato Commander. Os quatro baralhos de 2010 têm 15 esquemas distintos cada; Nicol Bolas tem 20 e os quatro de Duskmourn têm 10 cada. Promos isoladamente não são uma opção de baralho, pois são menos de 10 cartas.

## Funcionamento

- Acesso integrado à mesa Archenemy ou direto em `/esquemas`, sem login.
- Escolher coleção/baralho ou todos, embaralhar e tocar no verso para revelar.
- Embaralhamento Fisher–Yates com índices uniformes de `crypto.getRandomValues`.
- Esquemas resolvidos voltam ao fundo da fila; contínuos ficam ativos até abandono manual.
- Desfazer de até 30 ações nesta sessão; trocar/reembaralhar exige confirmação quando existe partida.
- Estado e ordem persistem em `localStorage`; recarregar não embaralha novamente.
- Texto das cartas disponível sem depender da imagem; imagens externas carregadas por HTTPS. Verso original da Eldritch Arena feito em CSS.
- A ferramenta não resolve efeitos, custos, cópias de esquemas, condições de abandono ou eliminações automaticamente. A mesa pode revelar esquemas adicionais quando um efeito exigir.
- Baralho e marcador de vida têm estados independentes: reiniciar os pontos não apaga os esquemas. Para nova partida completa, reinicie ambos.
- Uma sessão por navegador; não há sincronização multiplayer ou entre dispositivos. Imagens precisam de internet; não há garantia de reabrir a página offline. Cartas em inglês.

## Fontes e atualização

- Cartas/imagens: https://scryfall.com e https://api.scryfall.com/cards/search?q=t%3Ascheme&unique=prints
- Listas: https://mtgjson.com/api/v5/DeckList.json e os nove arquivos individuais de decks.
- Regra: https://magic.wizards.com/en/news/announcements/evolving-archenemy e regras completas 904.13.
- Duskmourn: https://magic.wizards.com/en/news/announcements/duskmourn-house-of-horror-commander-decklists

`node scripts/update-schemes.mjs` consulta as fontes públicas e escreve o catálogo somente ao concluir e validar as listas. Depois é necessário revisar o diff e executar os testes. Não há requisições de catálogo à API externa durante uma partida: o JSON é servido pelo próprio projeto. As imagens são referenciadas, não baixadas ou vendidas pelo projeto. Créditos e atribuição a Wizards, Scryfall e MTGJSON aparecem na interface.

## Verificação

33 testes JavaScript cobrindo marcador, migração dos presets, catálogo, embaralhamento, fila, contínuos, abandono, desfazer, confirmação, restauração, estado corrompido e falhas de armazenamento/rede. Testes Laravel verificam carregamento público das duas páginas e formatos de torneios. Revisão visual em navegador local incluindo imagem real e esquema contínuo. AWS não foi alterada.
