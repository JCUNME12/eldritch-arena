# Documentação Técnica — Eldritch Arena

O **Eldritch Arena** é uma aplicação web construída em Laravel para demonstrar a primeira etapa de um sistema voltado à organização de torneios e apoio a comunidades de card games. A arquitetura segue o padrão **MVC**, separando regras de controle, persistência de dados e apresentação em camadas distintas, conforme a estrutura convencional do Laravel.[1]

---

## 1. Visão Geral da Arquitetura

A aplicação utiliza controllers para coordenar requisições, models para representar as entidades principais e views Blade para renderizar as páginas. O fluxo foi construído com foco em um protótipo acadêmico demonstrável, priorizando clareza, organização e possibilidade de evolução.

| Camada | Responsabilidade | Arquivos principais |
|---|---|---|
| Rotas | Definir URLs públicas e protegidas | `routes/web.php` |
| Controllers | Processar requisições e regras de navegação | `app/Http/Controllers` |
| Models | Representar entidades do banco | `app/Models` |
| Views | Renderizar a interface | `resources/views` |
| Banco | Criar tabelas e dados iniciais | `database/migrations`, `database/seeders` |
| Assets | Organizar CSS, JavaScript e build | `resources/css`, `resources/js`, `public/build` |

---

## 2. Módulos Implementados

### 2.1 Autenticação

A autenticação contempla cadastro, login e logout. As rotas internas estão protegidas por middleware de autenticação, garantindo que apenas usuários logados acessem dashboard, torneios, marketplace e contador de vida.

### 2.2 Perfis de Usuário

O campo de tipo de usuário permite diferenciar **jogadores** e **organizadores**. Essa separação sustenta dashboards diferentes e permite futuras regras de permissão mais avançadas.

### 2.3 Torneios

O módulo de torneios oferece listagem, criação, detalhes e inscrição. Ele representa a funcionalidade central do sistema, pois conecta usuários, eventos e participação comunitária.

### 2.4 Marketplace Demonstrativo

O marketplace foi incluído como módulo conceitual para demonstrar potencial comercial e comunitário da plataforma. Nesta versão, ele funciona como vitrine demonstrativa.

### 2.5 Contador de Vida

O contador de vida oferece uma ferramenta rápida para uso durante partidas. Ele reforça o caráter prático da aplicação e aproxima o sistema da realidade dos jogadores.

### 2.6 PWA Inicial

O projeto inclui manifesto e service worker básico. Esse recurso prepara o caminho para instalação futura em dispositivos móveis e melhoria progressiva da experiência.[4]

---

## 3. Entidades do Banco de Dados

| Entidade | Campos principais | Finalidade |
|---|---|---|
| `users` | `name`, `email`, `password`, `type` | Gerenciar usuários e perfis |
| `tournaments` | `name`, `description`, `starts_at`, `status`, `created_by` | Armazenar eventos competitivos |
| `tournament_registrations` | `user_id`, `tournament_id` | Registrar inscrições |
| `card_listings` | `name`, `rarity`, `price`, `condition` | Exibir cartas no marketplace |

---

## 4. Rotas e Fluxo de Navegação

O fluxo básico começa na página inicial, passa por cadastro ou login, direciona o usuário para o dashboard e libera acesso aos módulos internos. As rotas protegidas foram agrupadas por middleware `auth`.

| Etapa | Rota | Resultado esperado |
|---|---|---|
| Entrada | `/` | Apresentação do sistema |
| Cadastro | `/cadastro` | Criação de conta |
| Login | `/login` | Autenticação |
| Painel | `/dashboard` | Área interna personalizada |
| Torneios | `/torneios` | Listagem e participação |
| Marketplace | `/marketplace` | Vitrine demonstrativa |
| Contador | `/contador-de-vida` | Ferramenta auxiliar |

---

## 5. Frontend e Identidade Visual

A interface foi construída com Blade e Tailwind CSS. O uso de Tailwind facilita a criação de layouts responsivos por classes utilitárias, mantendo consistência visual e velocidade de desenvolvimento.[2]

| Decisão visual | Justificativa |
|---|---|
| Tema escuro | Aproximação estética com o público gamer |
| Cards destacados | Organização de informações em blocos legíveis |
| Gradientes | Criação de identidade tecnológica |
| Navegação responsiva | Uso adequado em computadores e celulares |
| Componentes Blade | Reutilização de cabeçalho, layout e navegação |

---

## 6. Build e Execução

O frontend utiliza Vite para compilação de assets. O Vite é uma ferramenta moderna de build voltada a desenvolvimento rápido e empacotamento otimizado.[3]

```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
npm run build
php artisan serve
```

---

## 7. Validações Realizadas

Durante a preparação do protótipo, foram realizadas verificações de sintaxe PHP nos controllers, models, migrations, seeders e rotas. Também foi confirmada a presença dos arquivos PWA básicos e dos assets compilados pelo Vite.

| Validação | Resultado |
|---|---|
| Sintaxe PHP | Sem erros detectados nos arquivos criados |
| Estrutura MVC | Controllers, models e views organizados |
| Assets frontend | Build do Vite gerado |
| PWA básico | `manifest.json` e `service-worker.js` presentes |
| Documentação | README e documentação técnica criados |

---

## 8. Limitações da Primeira Versão

Esta primeira etapa foi planejada como protótipo acadêmico. Portanto, algumas funcionalidades permanecem como evolução futura, como pagamento de inscrições, ranking oficial, marketplace transacional, notificações em tempo real e painel administrativo completo.

---

## Referências

[1]: https://laravel.com/docs/11.x "Laravel 11 Documentation"  
[2]: https://tailwindcss.com/docs "Tailwind CSS Documentation"  
[3]: https://vite.dev/guide/ "Vite Guide"  
[4]: https://developer.mozilla.org/en-US/docs/Web/Progressive_web_apps "MDN Web Docs — Progressive Web Apps"  
