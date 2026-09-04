# Documentação Técnica — Eldritch Arena

## 1. Visão geral

O Eldritch Arena usa a arquitetura MVC do Laravel. As requisições HTTP passam pelas rotas e pelos middlewares, são tratadas pelos controllers, persistidas pelos models Eloquent no PostgreSQL e renderizadas em views Blade.

| Camada | Responsabilidade | Local principal |
|---|---|---|
| Rotas | Endpoints, autenticação, autorização e limites de uso | `routes/web.php` |
| Controllers | Validação e regras dos casos de uso | `app/Http/Controllers` |
| Models | Entidades, relacionamentos e conversões de tipos | `app/Models` |
| Banco | Estrutura, restrições, índices e dados demonstrativos | `database` |
| Interface | Telas Blade e assets Vite | `resources` |
| Infraestrutura | Imagem e orquestração dos serviços | `Dockerfile`, arquivos Compose |

## 2. Arquitetura de produção

O desenho recomendado na AWS é composto por:

1. Navegador acessando a aplicação na EC2.
2. Contêiner Apache/PHP executando Laravel 13.
3. Volume persistente para imagens enviadas.
4. Amazon RDS for PostgreSQL acessível somente a partir da EC2.
5. GitHub Actions validando alterações antes da publicação.

O banco não deve receber endereço público nem liberar `5432` para `0.0.0.0/0`. O grupo de segurança do RDS deve referenciar o grupo da EC2 como origem.

## 3. Banco de dados

PostgreSQL substitui o SQLite usado no protótipo. A mudança melhora concorrência, integridade, backup e operação em múltiplos processos.

| Entidade | Responsabilidade |
|---|---|
| `users` | Contas, perfis e estado premium |
| `tournaments` | Eventos publicados, cancelados ou finalizados |
| `tournament_registrations` | Participações únicas de usuários em torneios |
| `card_listings` | Anúncios do marketplace |
| `community_topics` | Tópicos e categorias da comunidade |
| `community_comments` | Comentários associados aos tópicos |
| `community_reactions` | Reações únicas por usuário, tópico e tipo |
| `sessions`, `cache`, `jobs` | Estado operacional do Laravel |

As chaves estrangeiras usam exclusão em cascata quando o registro filho não faz sentido sem o pai. Inscrições e reações possuem restrições únicas no banco, além das validações da aplicação.

### 3.1 Concorrência em inscrições

A inscrição em torneio ocorre em uma transação. O torneio é bloqueado durante a verificação de duplicidade, status, data e quantidade de vagas. Isso impede que duas requisições ocupem a última vaga ao mesmo tempo.

### 3.2 Migração do SQLite

O comando `eldritch:import-sqlite`:

- exige que as migrations já existam no destino;
- preserva IDs e chaves estrangeiras;
- copia somente colunas presentes nos dois bancos;
- importa tabelas na ordem de dependência;
- ajusta sequências do PostgreSQL;
- recusa um banco de destino preenchido sem `--replace`;
- executa a importação dentro de uma transação.

Os arquivos em `storage/app/public` precisam de cópia separada.

## 4. Autenticação e autorização

- Senhas usam o hash configurado pelo Laravel.
- A sessão é regenerada após login e invalidada no logout.
- Rotas internas exigem autenticação.
- O middleware `organizer` restringe a criação de torneios.
- Alterações e exclusões da comunidade verificam a propriedade do conteúdo.
- Login e cadastro têm limites próprios; outras gravações usam o limite `writes`.

## 5. Validação de entrada

Controllers validam tamanho, tipo e domínio dos dados antes da persistência. Marketplace, categorias, reações e planos aceitam somente valores conhecidos. Uploads são limitados a imagens de até 4 MB nos formatos autorizados.

O recurso de assinatura é apenas demonstrativo. Ele depende de `DEMO_PREMIUM_SUBSCRIPTION=true` e permanece forçado como falso no Compose de produção. Uma versão comercial deverá integrar um provedor de pagamentos e confirmar cobranças por webhook antes de liberar benefícios.

## 6. Contêiner de produção

O `Dockerfile` possui três estágios:

1. Node.js compila CSS e JavaScript.
2. Composer instala dependências PHP sem pacotes de desenvolvimento.
3. PHP 8.4 com Apache recebe somente os artefatos necessários.

A imagem instala `pdo_pgsql`, `intl`, `zip` e OPcache. O Apache serve apenas a pasta `public`. O health check consulta `/up`.

O entrypoint prepara diretórios, cria o link de armazenamento, gera caches e tenta executar migrations antes de iniciar o Apache. Uma falha persistente de conexão encerra o contêiner em vez de publicar uma aplicação parcialmente inicializada.

## 7. Persistência e backups

Há dois tipos de dados persistentes:

- PostgreSQL: snapshots e backups automatizados do RDS.
- Uploads: volume `eldritch_storage`, que requer backup próprio.

Uma restauração completa precisa dos dois. Segredos ficam em variáveis de ambiente e não fazem parte da imagem ou do repositório.

## 8. Pipeline de integração contínua

O workflow `.github/workflows/ci.yml` executa em `main`, branches `upgrade/**` e pull requests:

1. Instala PHP 8.4, Node.js 22 e as dependências.
2. Compila os assets.
3. Cria e popula um PostgreSQL 17 temporário.
4. Executa os testes.
5. Verifica formatação e vulnerabilidades PHP conhecidas.
6. Constrói a imagem Docker de produção.

O segundo estágio só roda se todas as verificações anteriores passarem.

## 9. Testes automatizados

A suíte cobre:

- disponibilidade das páginas públicas e do health check;
- proteção do dashboard;
- cadastro, login, logout e credenciais inválidas;
- autorização de organizadores e criação de torneios;
- inscrições duplicadas, lotação e cancelamento;
- criação e autorização de conteúdo da comunidade;
- validação do catálogo do marketplace;
- bloqueio do premium demonstrativo em produção;
- importação segura do SQLite legado.

## 10. Decisões ainda pendentes

Antes de uma abertura pública definitiva, recomenda-se implementar:

- verificação de e-mail e recuperação de senha;
- painel administrativo e moderação;
- HTTPS com domínio;
- serviço de e-mail transacional;
- observabilidade e alertas;
- política de privacidade e termos de uso;
- integração real de pagamentos;
- armazenamento de uploads em S3 caso a aplicação cresça horizontalmente.

## 11. Referências

- [Documentação do Laravel 13](https://laravel.com/docs/13.x)
- [Documentação do PostgreSQL](https://www.postgresql.org/docs/)
- [Documentação do Docker Compose](https://docs.docker.com/compose/)
- [Amazon RDS for PostgreSQL](https://docs.aws.amazon.com/AmazonRDS/latest/UserGuide/CHAP_PostgreSQL.html)
