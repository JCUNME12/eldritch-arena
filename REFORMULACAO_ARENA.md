# Eldritch Arena — reformulação de setembro de 2026

## Entrega

- Identidade visual própria: monograma E, cartas abstratas, grafite, verde e dourado; layout responsivo, navegação mobile e estados vazios.
- Removidos números fictícios, texto de banca/TCC na interface, exposição de credenciais e promessas de recursos inexistentes.
- Mesa pública com Magic Standard/Construído (20), Commander (40), Yu-Gi-Oh! TCG (8000), Speed Duel (4000) e configuração personalizada.
- Mesa de 1 a 6 participantes: nomes, cores, rotação, vida, veneno, energia, experiência, dano individual por comandante e parceiro, dados D4–D20, moeda, sorteio, desfazer e tela cheia quando suportada.
- Partida salva no navegador. Funciona enquanto a página permanecer aberta sem conexão; reabrir a página offline não é suportado. Histórico de desfazer limitado às 60 últimas ações da sessão.
- Torneios: formato por jogo, busca, filtros, meus eventos, edição pelo autor, cancelamento, inscritos, cancelamento de inscrição e bloqueio de lotação/data/inscrição própria. Transações e lock no PostgreSQL serializam operações sobre o mesmo torneio.
- Horários de formulário e exibição em Brasília, armazenamento no fuso configurado do Laravel para preservar compatibilidade com os dados existentes.
- Classificados: busca, ordenação, filtro pessoal, edição e remoção restritas ao autor. Contato por email; não existe checkout ou garantia de negociação.
- Comunidade: busca, categorias validadas, paginação e fluxos existentes preservados.
- Arena Plus: acesso antecipado gratuito com selo e ordenação para novos anúncios, ativação/desativação funcional. Sem cobrança ou promessas de benefícios não implementados.
- Cache antigo do service worker removido para não manter HTML com sessões ou CSRF antigos. Login e cadastro com limite de tentativas.
- Logs deixam de ser versionados; dados de exemplo bloqueados em produção.

## Executar e validar

```powershell
powershell -ExecutionPolicy Bypass -File scripts/postgresql.ps1 start
php artisan migrate
npm ci
npm run build
php artisan test
php vendor/bin/phpunit -c phpunit.pgsql.xml
npm run test:counter
```

A configuração de testes PostgreSQL usa apenas `eldritch_arena_test` com a role de testes. Não apontar testes com RefreshDatabase para dados reais.
Para instalar dados fictícios em um ambiente local novo: `php artisan db:seed`. Nunca executar o seeder para atualizar a instância na AWS.

## Verificações

- 16 testes PHP, 109 assertions (SQLite e PostgreSQL), incluindo entradas malformadas e conversão de fuso horário.
- 10 testes JavaScript: presets, confirmação, histórico, personalização, persistência, dano por comandante, marcadores, reinício, dados e recuperação de armazenamento.
- Build Vite, compilação das views e revisão no navegador em desktop e celular.
- Fluxos no navegador: login, painel, formato dependente do jogo, Commander, veneno, dano, restauração, dados, Yu-Gi-Oh! e desfazer.

## Limites e próximos incrementos

O marcador auxilia a partida; não é um juiz de regras nem valida decks. Avisos de vida, veneno e comandante não encerram a partida automaticamente, pois efeitos podem alterar condições de derrota. Two-Headed Giant e variantes com regras próprias não têm preset dedicado; use a mesa personalizada.
Não estão incluídos pagamentos, recuperação de senha por email, pareamento suíço, resultados/ranking de torneios, chat entre vendedores e compradores, notificações ou sincronização da mesa entre aparelhos. São integrações separadas, não botões simulados.
Backup, monitoramento e recuperação de dados da hospedagem continuam sendo necessários para operação pública duradoura.

## Referências dos presets

- Commander: https://magic.wizards.com/pt-BR/formats/commander
- Yu-Gi-Oh! TCG: https://www.yugioh-card.com/en/rulebook/
- Speed Duel: https://img.yugioh-card.com/eu/wp-content/uploads/2022/07/Speed_Duel_Guide_EN.pdf

## Publicação

Branch: `feature/arena-professional`. Checkpoint: `checkpoint/pre-arena-professional-20260928`.
Esta revisão é local e versionada no GitHub; a instância AWS não é atualizada automaticamente pelo push. Joao escolheu revisar localmente antes da publicação desta reformulação.
Para publicar, gerar backup do banco e do código vigente, levar a revisão testada com assets compilados, manter o `.env` da nuvem, aplicar as migrations e verificar HTTPS/login/uploads. Não copiar `.env` local, chaves, logs, dump ou banco de testes.
