# 🎮 Eldritch Arena — Plataforma TCC para Gestão de Torneios e Comunidade TCG

O **Eldritch Arena** é um protótipo web desenvolvido em **Laravel** para a primeira etapa do TCC, com foco em jogadores, organizadores e comunidades de card games. A proposta central é reunir, em uma única aplicação, recursos de **cadastro de usuários**, **gestão de torneios**, **dashboard por perfil**, **marketplace demonstrativo de cartas** e **contador de vida digital**, formando uma base funcional para evoluções futuras do produto.

---

## 👥 Integrantes do Projeto

| Integrante | Função no Projeto |
|---|---|
| João Carlos Campos | Idealização, desenvolvimento e documentação |

---

## 📌 Descrição do Serviço

O **Eldritch Arena** atua como uma plataforma de apoio para comunidades de jogos de cartas colecionáveis. O sistema permite que usuários se cadastrem como **jogadores** ou **organizadores**, visualizem torneios, realizem inscrições, acompanhem indicadores por perfil e utilizem ferramentas auxiliares para eventos presenciais ou online.

> A primeira versão do projeto foi planejada como um **protótipo funcional demonstrável**, priorizando telas navegáveis, fluxo de autenticação, estrutura de banco de dados, identidade visual própria e funcionalidades suficientes para apresentação acadêmica.

---

## 🧭 Objetivo do Projeto

O objetivo do projeto é desenvolver uma solução web que facilite a organização de torneios e a interação entre jogadores de card games. Nesta primeira parte do TCC, o foco está em validar a viabilidade técnica e visual da plataforma por meio de uma aplicação Laravel com autenticação, rotas protegidas, dashboards personalizados e módulos demonstrativos.

| Objetivo | Como foi aplicado no protótipo |
|---|---|
| Organizar torneios | Tela de listagem, criação, detalhes e inscrição em torneios |
| Diferenciar tipos de usuários | Perfis de jogador e organizador com dashboards específicos |
| Apoiar a comunidade TCG | Marketplace demonstrativo e contador de vida digital |
| Preparar evolução futura | Estrutura em MVC, migrations, seeders e componentes reutilizáveis |

---

## ⚙️ Responsabilidades da Aplicação

O protótipo concentra as responsabilidades essenciais para uma plataforma inicial de torneios e comunidade. O sistema realiza o controle de autenticação, organiza usuários por tipo de perfil e fornece telas específicas para as ações principais da aplicação.

- **Autenticação de usuários:** cadastro, login, logout e proteção de rotas autenticadas.
- **Perfis de acesso:** diferenciação entre jogador e organizador.
- **Gestão de torneios:** criação, listagem, visualização e inscrição em torneios.
- **Dashboard personalizado:** indicadores e ações adequadas para cada tipo de usuário.
- **Marketplace demonstrativo:** vitrine inicial de cartas e ofertas para validação de conceito.
- **Contador de vida:** ferramenta auxiliar para partidas de card games.
- **PWA básico:** manifesto e service worker inicial para evolução mobile-first.

---

## 🛠️ Tecnologias Utilizadas

| Tecnologia | Uso no Projeto |
|---|---|
| **PHP** | Linguagem principal do backend |
| **Laravel 11** | Framework MVC utilizado para rotas, controllers, models, migrations e views |
| **Blade** | Engine de templates para construção das telas |
| **MySQL/MariaDB** | Banco de dados recomendado para execução local via XAMPP |
| **Tailwind CSS** | Estilização responsiva com identidade visual dark gamer |
| **Alpine.js** | Interações leves no frontend |
| **Chart.js** | Base para gráficos e indicadores visuais |
| **Vite** | Compilação dos assets frontend |
| **PWA** | Manifesto e service worker básico para evolução progressiva |

---

## 📁 Estrutura Principal

A aplicação segue a organização padrão do Laravel, separando responsabilidades entre controllers, models, views, migrations e arquivos públicos. Essa separação facilita manutenção, evolução do TCC e futuras integrações.

```text
eldritch-arena/
├── app/
│   ├── Http/Controllers/
│   │   ├── Auth/
│   │   ├── DashboardController.php
│   │   ├── MarketplaceController.php
│   │   ├── LifeCounterController.php
│   │   ├── ProfileTypeController.php
│   │   └── TournamentController.php
│   └── Models/
│       ├── User.php
│       ├── Tournament.php
│       ├── TournamentRegistration.php
│       └── CardListing.php
├── database/
│   ├── migrations/
│   └── seeders/
├── resources/
│   ├── css/
│   ├── js/
│   └── views/
├── public/
│   ├── manifest.json
│   └── service-worker.js
├── routes/
│   └── web.php
└── README.md
```

---

## 🗃️ Modelagem Inicial do Banco de Dados

O banco foi estruturado para permitir cadastro de usuários, criação de torneios, inscrições e listagem de cartas. A modelagem inicial não pretende encerrar o escopo completo do TCC, mas fornecer uma base consistente para apresentação da primeira etapa.

| Entidade | Finalidade |
|---|---|
| `users` | Armazena usuários autenticados e o tipo de perfil |
| `tournaments` | Registra torneios criados por organizadores |
| `tournament_registrations` | Relaciona jogadores inscritos aos torneios |
| `card_listings` | Armazena anúncios demonstrativos do marketplace |

---

## 🔐 Perfis de Usuário

O sistema trabalha com dois perfis principais. Essa separação ajuda a demonstrar que a plataforma pode atender públicos diferentes dentro do mesmo ecossistema de card games.

| Perfil | Permissões e Experiência |
|---|---|
| **Jogador** | Visualiza torneios, realiza inscrições, acessa marketplace e usa o contador de vida |
| **Organizador** | Cria torneios, acompanha inscrições e visualiza indicadores de eventos |

---

## 🧩 Funcionalidades Implementadas

### 🏠 Página Inicial

A página inicial apresenta a proposta do Eldritch Arena com identidade visual dark gamer, chamadas para cadastro e login, além de uma descrição objetiva da solução.

### 🔑 Autenticação

O fluxo de autenticação inclui cadastro, login e logout. As áreas internas ficam protegidas por middleware de autenticação, impedindo acesso direto sem sessão válida.

### 📊 Dashboard

Após o login, o usuário acessa um dashboard adaptado ao tipo de perfil. Jogadores recebem informações voltadas à participação, enquanto organizadores visualizam atalhos e indicadores relacionados à gestão de eventos.

### 🏆 Torneios

O módulo de torneios permite listar eventos, abrir detalhes, criar novos torneios e realizar inscrição. Essa funcionalidade representa o núcleo da proposta do TCC.

### 🃏 Marketplace Demonstrativo

O marketplace funciona como uma vitrine inicial de cartas e ofertas. Nesta etapa, ele serve para validar a expansão futura da plataforma para compra, venda ou troca de cards.

### ❤️ Contador de Vida

O contador de vida é uma ferramenta prática para partidas, pensada para uso rápido durante jogos presenciais. Ele reforça a utilidade da aplicação para o público final.

### 📱 PWA Básico

A aplicação inclui `manifest.json` e `service-worker.js`, preparando o caminho para uma experiência progressiva em dispositivos móveis.

---

## 🌐 Rotas Principais

| Método | Rota | Nome | Descrição |
|---|---|---|---|
| `GET` | `/` | `home` | Página inicial do projeto |
| `GET` | `/cadastro` | `register` | Formulário de cadastro |
| `POST` | `/cadastro` | `register` | Criação de usuário |
| `GET` | `/login` | `login` | Formulário de login |
| `POST` | `/login` | `login` | Autenticação do usuário |
| `POST` | `/logout` | `logout` | Encerramento da sessão |
| `GET` | `/dashboard` | `dashboard` | Painel interno do usuário |
| `PATCH` | `/perfil/tipo` | `profile.type` | Atualização do tipo de perfil |
| `GET` | `/torneios` | `tournaments.index` | Listagem de torneios |
| `GET` | `/torneios/criar` | `tournaments.create` | Tela de criação de torneios |
| `POST` | `/torneios` | `tournaments.store` | Cadastro de novo torneio |
| `GET` | `/torneios/{tournament}` | `tournaments.show` | Detalhes de um torneio |
| `POST` | `/torneios/{tournament}/inscrever` | `tournaments.register` | Inscrição em torneio |
| `GET` | `/marketplace` | `marketplace` | Vitrine demonstrativa de cartas |
| `GET` | `/contador-de-vida` | `life-counter` | Contador de vida digital |

---

## 🚀 Como Executar Localmente

Antes de iniciar, garanta que o ambiente tenha **PHP**, **Composer**, **Node.js**, **npm** e um banco **MySQL/MariaDB** disponíveis. Em ambiente Windows, o projeto pode ser executado com XAMPP para facilitar a configuração do Apache, PHP e MySQL.

```bash
# 1. Clonar o repositório
git clone URL_DO_REPOSITORIO
cd eldritch-arena

# 2. Instalar dependências PHP
composer install

# 3. Instalar dependências frontend
npm install

# 4. Criar arquivo de ambiente
cp .env.example .env

# 5. Gerar chave da aplicação
php artisan key:generate

# 6. Configurar o banco no .env
DB_DATABASE=eldritch_arena
DB_USERNAME=root
DB_PASSWORD=

# 7. Rodar migrations e seeders
php artisan migrate --seed

# 8. Compilar assets
npm run build

# 9. Iniciar servidor local
php artisan serve
```

Após iniciar o servidor, acesse a aplicação em:

```text
http://127.0.0.1:8000
```

---

## 🧪 Dados Demonstrativos

O projeto possui seeders para popular a aplicação com registros iniciais. Esses dados facilitam a apresentação, pois permitem demonstrar o fluxo do sistema sem cadastrar todas as informações manualmente.

| Tipo de dado | Uso na apresentação |
|---|---|
| Usuários | Testar login e perfis |
| Torneios | Demonstrar listagem, detalhes e inscrição |
| Cartas | Exibir marketplace demonstrativo |

---

## 🎨 Identidade Visual

A interface foi criada com uma estética **dark gamer**, utilizando tons escuros, contraste em roxo, azul e ciano, cards com bordas suaves e navegação responsiva. Essa escolha visual busca aproximar a plataforma do universo de jogos competitivos e card games.

| Elemento visual | Intenção |
|---|---|
| Fundo escuro | Criar atmosfera gamer e reduzir distração visual |
| Gradientes roxo/azul | Dar identidade tecnológica e competitiva |
| Cards arredondados | Organizar informações de forma limpa |
| Navegação inferior mobile | Facilitar uso em telas pequenas |
| Componentes reutilizáveis | Manter consistência entre páginas |

---

## 📱 Escopo PWA

O PWA foi incluído em nível inicial para demonstrar visão de evolução mobile. Nesta etapa, a aplicação já possui manifesto e service worker básico, mas ainda pode evoluir com cache avançado, instalação orientada e notificações.

| Recurso | Situação |
|---|---|
| `manifest.json` | Implementado |
| `service-worker.js` | Implementado |
| Ícones finais | Previsto para evolução |
| Cache avançado | Previsto para evolução |
| Notificações push | Previsto para evolução futura |

---

## 🧱 Possíveis Evoluções para as Próximas Etapas

O protótipo foi construído para permitir crescimento gradual. As próximas etapas podem transformar a aplicação em uma plataforma mais completa, com integrações externas e recursos avançados de comunidade.

| Evolução | Descrição |
|---|---|
| Sistema de rankings | Pontuação por torneio, histórico e classificação de jogadores |
| Pagamentos | Inscrições pagas e controle financeiro de eventos |
| Marketplace real | Compra, venda e troca de cartas entre usuários |
| Chat ou comunidade | Comunicação entre jogadores e organizadores |
| QR Code de check-in | Confirmação rápida de presença em torneios |
| Notificações | Alertas sobre torneios, inscrições e resultados |
| Painel administrativo | Moderação, relatórios e gestão geral da plataforma |

---

## ✅ Status do Protótipo

| Área | Status |
|---|---|
| Base Laravel | Concluída |
| Autenticação | Implementada |
| Perfis de usuário | Implementados |
| Torneios | Implementados |
| Marketplace demonstrativo | Implementado |
| Contador de vida | Implementado |
| PWA básico | Implementado |
| Documentação | Concluída |

---

## 📚 Referências

[1]: https://laravel.com/docs/11.x "Laravel 11 Documentation"  
[2]: https://tailwindcss.com/docs "Tailwind CSS Documentation"  
[3]: https://vite.dev/guide/ "Vite Guide"  
[4]: https://developer.mozilla.org/en-US/docs/Web/Progressive_web_apps "MDN Web Docs — Progressive Web Apps"  
