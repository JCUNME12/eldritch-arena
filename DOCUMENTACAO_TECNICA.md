# Documentação técnica — Eldritch Arena

Estado atualizado em 26/10/2026. Veja o [README](README.md) para instalação, funcionalidades e limitações.

## Arquitetura

Aplicação Laravel 13 com páginas Blade, Tailwind CSS e interações Alpine.js. Controllers processam requisições; models usam Eloquent/PostgreSQL; migrations versionam o esquema. O contador e o baralho de esquemas executam no navegador e persistem estado localmente.

| Componente | Responsabilidade |
| --- | --- |
| `routes/web.php` | Rotas públicas e grupos protegidos |
| `DashboardController` | Painéis por perfil e anúncios disponíveis |
| `TournamentController` | Eventos, inscrições, capacidade, alterações e cancelamento |
| `MarketplaceController` | Vitrine e anúncios com autorização do proprietário |
| `CommunityController` | Tópicos, comentários e reações |
| `StoreController` | Perfil da loja, produtos e operações de estoque |
| `InventoryService` | Transações de saldo, histórico e sincronização da vitrine |
| `PlayerDeckController` / `PlayerInsights` | Histórico privado e análises Premium de decks e confrontos |
| `ShowcaseController` / `DemoPaymentService` | Lojas demo, checkout simulado, estados de pagamento e assinatura |
| `AdminController` | Consulta de contas e indicadores |
| `EnsureStoreAccess` / `EnsureAdmin` | Controle de acesso às áreas restritas |
| `resources/js/life-counter.js` | Formatos, equipes, marcadores, sorteios e desfazer |
| `resources/js/scheme-deck.js` | Embaralhamento, revelação, contínuos e persistência |

## Dados e permissões

`users` guarda o perfil jogador/organizador e o privilégio separado `is_admin`. `tournaments` e `tournament_registrations` representam eventos/inscrições. `card_listings` contém anúncios avulsos ou vinculados a estoque. A comunidade usa `community_topics`, `community_comments` e `community_reactions`.

O estoque usa `stores` (uma por proprietário), `inventory_items` (SKU único por loja), `stock_movements` (autor, motivo, saldo anterior/final e identificador único da operação) e `admin_access_logs` (concessões/revogações administrativas).

As rotas de loja exigem autenticação e permissão; cada produto também verifica propriedade. Admin não ganha acesso de escrita ao estoque de outras lojas. Cadastro e troca de perfil não atribuem `is_admin`. A concessão usa `php artisan arena:admin email-da-conta`, preservando senha e perfil; `--revoke` revoga e mantém auditoria.

## Integridade do estoque

Movimentações ocorrem em transações com bloqueio da linha do item. Não permitem saldo negativo e usam identificador único para impedir duplicidade de envio. Alterar os metadados do produto não altera o saldo. Quantidade, publicação e arquivamento não são campos de atribuição em massa.

Uma publicação gera um único anúncio vinculado. O escopo de disponibilidade exclui itens sem saldo, pausados ou arquivados. Reposição torna o item disponível se a publicação continuar ativa. O histórico é preservado; arquivamento exige saldo zero. Custos não são expostos na vitrine. O total de custo é quantidade atual × custo unitário atual, sem significado contábil de lucro.

## Rotas de referência

Públicas: `/`, `/login`, `/cadastro`, `/contador-de-vida`, `/esquemas`, `/up`.
Autenticadas: `/dashboard`, `/torneios`, `/marketplace`, `/comunidade`, `/premium`.
Restritas: `/loja` e seus produtos/movimentações; `/admin`.
Consulte `php artisan route:list` para métodos e parâmetros atuais.

## Frontend e implantação

Vite gera `public/build/manifest.json` e assets com nomes versionados. O catálogo de esquemas fica em `public/data/archenemy-schemes.json`, com fontes documentadas em [ESQUEMAS_ARCHENEMY.md](ESQUEMAS_ARCHENEMY.md). O service worker remove caches antigos da aplicação e não armazena HTML autenticado.

Na VPS, Caddy termina HTTPS e encaminha ao Apache/PHP; o PostgreSQL tem volume persistente. A configuração implantada confia no proxy para preservar URLs HTTPS. Esse ajuste é específico do ambiente atual; veja [DEPLOY_AWS.md](DEPLOY_AWS.md), inclusive as diferenças entre repositório e configuração operacional.

## Verificação

Testes PHP cobrem autorização, estoque, idempotência, validações, anúncios, administração e fluxos da plataforma. Testes JavaScript cobrem formatos, recuperação de mesas, equipes, dados e baralhos. Contagens e execução recente estão no README. Pagamentos reais, moderação administrativa ampla e sincronização de mesas não estão implementados.


## Mostra e planos

`stores.is_demo` e `demo_presenter_id` isolam os conjuntos fictícios. `demo_payments` registra propósito (venda/assinatura), estado, snapshots monetários em centavos, método, token único de envio e eventos. Aprovação e estorno usam transação, bloqueando primeiro a loja, depois o pagamento e o item; as movimentações usam `InventoryService`. Repetir o estado atual não duplica operações; estados terminais não reabrem pagamentos. Assinaturas demo modificam apenas o proprietário fictício da loja.

O relatório de vendas exige Plus e autorização sobre a demo. A exportação real `/loja/relatorio-estoque` exige Plus e retorna somente o estoque do próprio usuário, protegendo nomes e SKUs contra fórmulas CSV. A interface distingue projeção do estoque de receita e margem simuladas. Consulte [MOSTRA_FACULDADE.md](MOSTRA_FACULDADE.md) para roteiro, limites e preparação.


## Histórico de jogadores

As tabelas `player_decks` e `player_matches` preservam resultados privados e removíveis de forma reversível. A análise é restrita ao proprietário com Plus ativo e os recortes separam o número de jogadores. A prévia em `/meus-decks/exemplo` não persiste dados. Veja [JOGADOR_PREMIUM.md](JOGADOR_PREMIUM.md) para fórmulas, filtros, limitações e roteiro.
