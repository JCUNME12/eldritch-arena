# Eldritch Arena

Plataforma web para jogadores, organizadores e comunidades de card games. O projeto reúne autenticação, perfis, torneios, inscrições, marketplace, comunidade e contador de vida em uma aplicação preparada para execução local e publicação na AWS.

## Situação atual

- Laravel 13 e PHP 8.4 na imagem de produção.
- PostgreSQL como banco principal.
- Docker Compose para desenvolvimento e produção.
- Migração assistida do antigo SQLite para PostgreSQL.
- Testes automatizados de autenticação, conta, administração, torneios, marketplace e comunidade.
- Pipeline no GitHub Actions com testes, análise de estilo, auditoria e build da imagem.

## Tecnologias

| Camada | Tecnologia |
|---|---|
| Backend | PHP 8.4, Laravel 13 |
| Frontend | Blade, Tailwind CSS, Alpine.js, Vite |
| Banco | PostgreSQL 17 |
| Servidor | Apache em contêiner Docker |
| Infraestrutura | Docker Compose, AWS EC2 e Amazon RDS |
| Qualidade | PHPUnit, Laravel Pint, Composer Audit, GitHub Actions |

## Funcionalidades

- Cadastro, login, logout e perfis de jogador ou organizador.
- Criação, edição e cancelamento de torneios por organizadores.
- Inscrições protegidas contra duplicidade e lotação, com cancelamento pelo jogador.
- Marketplace de cartas com catálogo validado e gestão dos próprios anúncios.
- Comunidade com tópicos, comentários, imagens e reações.
- Contador de vida para partidas.
- Área de conta para atualização de perfil e senha.
- Painel administrativo para permissões, torneios, anúncios e moderação.

## Execução local com Docker

Pré-requisito: Docker Desktop com Docker Compose.

1. Copie `.env.example` para `.env`.
2. Gere uma chave com PHP e coloque o resultado em `APP_KEY`:

```bash
php -r "echo 'base64:'.base64_encode(random_bytes(32)).PHP_EOL;"
```

3. Defina uma senha forte em `DB_PASSWORD`.
4. Inicie a aplicação e o PostgreSQL:

```bash
docker compose up -d --build
```

5. Para carregar conteúdo inicial no ambiente de desenvolvimento, execute uma vez:

```bash
docker compose exec app php artisan db:seed --force
```

A aplicação ficará disponível em `http://localhost:8080`. O PostgreSQL local só é exposto em `127.0.0.1:5432`.

Para encerrar sem apagar os dados:

```bash
docker compose down
```

Não use `docker compose down -v` se quiser preservar o banco e os arquivos enviados.

## Execução sem Docker

Requisitos: PHP 8.3 ou superior com `pdo_pgsql`, Composer, Node.js 22 ou superior e PostgreSQL.

```bash
composer install
npm ci
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
npm run build
php artisan serve
```

Configure primeiro as variáveis `DB_*` no `.env`. Em Windows, a cópia do arquivo pode ser feita com `Copy-Item .env.example .env`.

## Migrar os dados do SQLite antigo

Faça backup do arquivo antigo antes de importar. Com o PostgreSQL configurado e as migrations executadas:

```bash
php artisan eldritch:import-sqlite --source="C:\caminho\para\database.sqlite"
```

O comando preserva os IDs e relacionamentos, importa apenas colunas compatíveis e se recusa a sobrescrever um destino que já tenha dados. Para substituir os dados existentes intencionalmente, após criar um backup:

```bash
php artisan eldritch:import-sqlite --source="C:\caminho\para\database.sqlite" --replace
```

As imagens não ficam dentro do SQLite. Copie também o conteúdo antigo de `storage/app/public` para o volume persistente da aplicação.

## Testes e verificações

```bash
php artisan test
vendor/bin/pint --test
composer validate --strict
composer audit
npm run build
```

O pipeline repete essas verificações usando PostgreSQL real e, depois, constrói a imagem de produção.

## Criar um administrador

Execute o comando abaixo em um terminal interativo. A senha é solicitada de forma oculta e não é armazenada no histórico nem no repositório:

```bash
docker compose exec app php artisan app:admin admin@example.com --name="Administrador"
```

Para automações, o comando também aceita a senha pela variável temporária `ELDRITCH_ADMIN_PASSWORD`. Remova essa variável do ambiente após o uso.

## Publicação na AWS

A arquitetura indicada separa aplicação e banco:

- Uma instância EC2 executa Docker e o contêiner do Eldritch Arena.
- Um banco Amazon RDS for PostgreSQL fica em sub-redes privadas.
- A porta `5432` do RDS aceita tráfego somente do grupo de segurança da EC2.
- A EC2 publica inicialmente a porta `8080`; para uso definitivo, prefira HTTPS por meio de um proxy ou balanceador.
- Arquivos enviados usam o volume Docker `eldritch_storage`.

Copie `.env.production.example` para um arquivo seguro, preencha os segredos sem versioná-los e execute:

```bash
docker compose --env-file .env.production -f compose.production.yaml up -d --build
```

No Portainer, use o repositório Git e informe `compose.production.yaml` como caminho do Compose. Cadastre as variáveis de ambiente pela interface e nunca salve senhas ou chaves no repositório.

## Cuidados de produção

- `APP_DEBUG` permanece desativado.
- A conexão com PostgreSQL exige SSL por padrão.
- Login, cadastro e gravações sensíveis possuem limitação de tentativas.
- Apenas organizadores criam torneios.
- O painel administrativo exige uma conta marcada explicitamente como administradora.
- O processo de inicialização executa migrations e só inicia após o banco responder.
- Não execute `db:seed` em produção: o seeder é destinado somente ao desenvolvimento local.

## Contas locais de desenvolvimento

Somente para desenvolvimento, após `db:seed`. Essas credenciais não são exibidas pela interface:

| Perfil | E-mail | Senha |
|---|---|---|
| Jogador | `jogador@eldritch.test` | `password` |
| Organizador | `loja@eldritch.test` | `password` |

## Documentação

As decisões de arquitetura, segurança, banco e implantação estão detalhadas em [DOCUMENTACAO_TECNICA.md](DOCUMENTACAO_TECNICA.md).

## Licença

Projeto Eldritch Arena, desenvolvido por João Carlos Campos.
