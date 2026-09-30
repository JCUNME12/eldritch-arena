# Eldritch Arena

Plataforma para comunidades de card games, com torneios, marketplace, comunidade, ferramentas de mesa e estoque para lojistas. Desenvolvida por **João Carlos Campos**, no contexto do TCC, com identidade visual própria e interface responsiva.

## Estado atual — 30/09/2026

A reformulação está publicada na AWS: https://18.224.27.22.sslip.io/.
O desenvolvimento atual está na branch `feature/arena-professional`. A publicação é manual; enviar um commit ao GitHub não atualiza automaticamente a VPS ou seu banco.

| Área | Disponível |
| --- | --- |
| Contas | Cadastro, login, logout e perfis jogador/organizador |
| Torneios | Busca, formatos, criação, edição, cancelamento e inscrições com controle de vagas |
| Marketplace | Anúncios, filtros e gerenciamento pelo proprietário; integração com estoque |
| Comunidade | Tópicos, comentários e reações; edição e exclusão autorizadas |
| Mesa | Escolha de Magic ou Yu-Gi-Oh!, formatos, vida, marcadores, dano de comandante, dados, moeda e sorteios |
| Archenemy | Regra Commander: 60 de vida do arqui-inimigo e 60 compartilhados pelos aliados; esquemas embaralhados, revelação e esquemas contínuos |
| Minha loja | SKU, custo/preço, saldo, alertas, entradas/saídas com histórico, publicação e pausa |
| Administração | Consulta de contas e indicadores, com privilégio independente do perfil |

## Limites do produto

Não há processamento de pagamentos, checkout, entrega, reserva de estoque ou baixa automática por venda. As negociações ocorrem fora da plataforma e o lojista registra as saídas. Valores previstos de inscrições não representam dinheiro recebido. Arena Plus não representa uma cobrança integrada.

O painel administrativo é inicialmente de consulta. A mesa salva seu estado no navegador, sem sincronização entre aparelhos nem execução automática dos efeitos das cartas. Imagens de esquemas dependem de conexão externa. O service worker não armazena páginas autenticadas ou tokens; não há promessa de funcionamento integral offline.

## Tecnologias e organização

PHP 8.4.1+ (8.x), Laravel 13, PostgreSQL 18, Blade, Tailwind CSS, Alpine.js e Vite. Na AWS, PHP/Apache, PostgreSQL e Caddy rodam em contêineres Docker Compose na mesma EC2; o banco não usa RDS nesta implantação.

- `app/Http`: controllers e middleware de autenticação/permissões.
- `app/Models` e `app/Services`: entidades e regras, incluindo movimentação de estoque.
- `database/migrations`: evolução do banco; seeders são dados opcionais de demonstração.
- `resources/views`, `resources/css`, `resources/js`: telas, estilo, contador e esquemas.
- `routes/web.php`: rotas; `public/build`: arquivos compilados.
- `tests`: testes PHP e JavaScript.

## Executar localmente

Instale PHP com `pdo_pgsql`, Composer, Node.js/npm e PostgreSQL. No Windows com Herd, veja [POSTGRESQL.md](POSTGRESQL.md).

```sh
git clone https://github.com/JCUNME12/eldritch-arena.git
cd eldritch-arena
git switch feature/arena-professional
composer install
npm ci
```

Copie `.env.example` para `.env`, crie um banco/usuário PostgreSQL e configure `DB_CONNECTION=pgsql`, host, porta, banco, usuário e senha. Defina `APP_URL` conforme o endereço local. Não compartilhe o `.env`.

```sh
php artisan key:generate
php artisan migrate
npm run build
php artisan serve
```

Com o servidor Artisan, acesse http://127.0.0.1:8000; com Herd configurado, http://eldritch-arena.test. A geração da chave é somente para uma instalação nova: não substitua a chave de um ambiente existente. Seeders são opcionais em banco de demonstração; não os execute indiscriminadamente em produção.

## Testes

```sh
php vendor/bin/phpunit
npm run test:counter
npm run build
php artisan view:cache
```

PostgreSQL: configure o banco exclusivo de testes conforme [POSTGRESQL.md](POSTGRESQL.md) e execute `php vendor/bin/phpunit -c phpunit.pgsql.xml`. Nunca use o banco do site nos testes.

Na validação de 30/09: 28 testes PHP / 230 asserções e 33 testes JavaScript passaram. A suíte PostgreSQL também passou na validação de 28/09. Em 30/09, o sandbox bloqueou os subprocessos do build; a publicação reutilizou os assets aprovados em 28/09, sem alterações posteriores em CSS/JS. Login, páginas autenticadas, estoque, esquemas, assets e HTTPS foram verificados na VPS.

## Documentação

- [Arquitetura e regras](DOCUMENTACAO_TECNICA.md)
- [Loja, estoque e administração](LOJA_E_ADMIN.md)
- [Publicação e operação na AWS](DEPLOY_AWS.md)
- [Banco local e testes PostgreSQL](POSTGRESQL.md)
- [Formatos de Magic](FORMATOS_MAGIC.md)
- [Esquemas de Archenemy e fontes](ESQUEMAS_ARCHENEMY.md)
- [Histórico da reformulação](REFORMULACAO_ARENA.md)
- [Atualização do Laravel](UPGRADE_LARAVEL_13.md)
