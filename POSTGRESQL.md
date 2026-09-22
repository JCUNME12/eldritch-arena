# PostgreSQL — Eldritch Arena

O ambiente local usa PostgreSQL 18.6, em 127.0.0.1:5432. O banco do site e eldritch_arena; o usuario e eldritch_app. A senha esta no .env, ignorado pelo Git. A conta nao e superusuario e nao cria bancos ou usuarios.

## Iniciar depois de reiniciar o Windows

Na pasta do projeto, execute no PowerShell:

```powershell
powershell -ExecutionPolicy Bypass -File scripts/postgresql.ps1 start
```

O servidor roda em segundo plano. Nao foi instalado como servico automatico. Use `status` para consultar e `stop` para encerrar. O Herd continua servindo http://eldritch-arena.test.

Os binarios oficiais EDB ficam em `.local/postgresql/pgsql`; os dados em `.local/pgdata`. Nao apague `.local`: ela contem o banco, backups e credenciais locais. Essa pasta foi restrita ao usuario Windows atual e e ignorada pelo Git. Ela nao e recriada por composer install. Em outro computador, instale PostgreSQL 18, crie um usuario e banco e configure o .env antes das migrations.

## Configuracao

Use .env.example como referencia; a senha deve ser definida localmente. Para um banco novo, rode `php artisan migrate`. O seed e opcional para dados demonstrativos; nao o rode sobre dados migrados para evitar sobrescrever cadastros de demonstracao.

## Testes

Os testes rapidos continuam isolados em SQLite em memoria:

```powershell
php artisan test
```

Para testar PostgreSQL:

```powershell
php vendor/phpunit/phpunit/phpunit --configuration phpunit.pgsql.xml
```

Configure .env.testing com APP_ENV=testing, uma APP_KEY valida e a senha do usuario eldritch_test em DB_PASSWORD. O banco eldritch_arena_test e exclusivo dos testes: suas tabelas podem ser recriadas. O arquivo phpunit.pgsql.xml fixa a conexao pgsql, o banco eldritch_arena_test e o usuario eldritch_test, e desativa DB_URL. O usuario de testes nao tem acesso ao banco do site. Nunca use credenciais de producao nos testes.

## Migracao realizada em 21/09/2026

Checkpoint Git: checkpoint/pre-postgresql-20260921 (89dbc6c). Branch: migration/postgresql.

Backup SQLite consistente e copia do .env anterior: `.local/backups/pre-postgresql-20260921/`. O arquivo database/database.sqlite original tambem foi preservado. SHA-256 do snapshot: 9a11f133eecf5cf13d535ebd2f8898bb339f5f5e2d57846778e6adee9e4bfdbd.

As 13 migrations foram aplicadas no PostgreSQL, sem alterar seus arquivos. Dados transferidos em uma transacao, com chaves estrangeiras ativas, preservacao dos IDs, senhas e timestamps, conversao dos booleanos e sincronizacao das sequencias. Os registros foram comparados campo a campo por fingerprints normalizados antes do commit; valores numericos foram normalizados para a precisao usada neste schema.

| Tabela | Registros transferidos |
| --- | ---: |
| users | 2 |
| tournaments | 3 |
| tournament_registrations | 1 |
| card_listings | 6 |
| community_topics | 4 |
| community_comments | 4 |
| community_reactions | 5 |
| sessions | 2 |
| password_reset_tokens, cache, cache_locks, jobs, job_batches, failed_jobs | 0 |

A tabela migrations foi gerada pelo Laravel no destino. Nenhum seed foi executado no banco do site. APP_KEY e arquivos de uploads foram preservados. Novos dados passam a ser gravados somente no PostgreSQL.

O comando `php artisan database:import-sqlite CAMINHO_DO_SNAPSHOT` pode importar esta estrutura em outro PostgreSQL previamente migrado e vazio. Ele rejeita tabelas inesperadas, colunas divergentes e destino ocupado. Use com a aplicacao e workers parados para evitar escritas durante a transferencia. Ele carrega cada tabela em memoria e foi feito para este banco pequeno; nao e uma ferramenta geral para bases grandes.

## AWS

Use Amazon RDS for PostgreSQL, escolhendo uma versao 18.x disponivel na regiao. Configure endpoint, porta, usuario, senha e banco no ambiente de deploy. Para TLS com verificacao de identidade, use DB_SSLMODE=verify-full e DB_SSLROOTCERT com o caminho do certificado CA do RDS. Restrinja o acesso de rede a aplicacao. Nao use os binarios, credenciais ou a pasta de dados local como configuracao de producao.

Referencias: https://www.postgresql.org/download/windows/ e https://docs.aws.amazon.com/AmazonRDS/latest/PostgreSQLReleaseNotes/postgresql-versions.html.

## Retorno ao SQLite

Antes de voltar, pare as escritas e faca um backup PostgreSQL com pg_dump. A copia SQLite representa o instante da migracao: voltar a ela nao inclui novos dados gravados no PostgreSQL. Restaure o .env do backup e execute `php artisan config:clear`; o arquivo SQLite original foi preservado. Para reverter tambem o codigo, use o checkpoint Git depois de guardar alteracoes posteriores. Nunca sobrescreva dados atuais sem comparar o que foi criado apos a migracao.

## Validacao final

- PostgreSQL: 7 testes / 49 assertions aprovados, incluindo migrations e seed em banco isolado.
- SQLite em memoria: 7 testes / 49 assertions aprovados.
- 13 migrations aplicadas no banco do site.
- Banco e usuario ativos confirmados: eldritch_arena / eldritch_app.
- Site HTTP 200 pelo Herd, apos reiniciar o PHP 8.4 para resolver um erro de gateway.
- Importacao repetida rejeitada com codigo 1; dados existentes preservados.
- Backup PostgreSQL em `.local/backups/eldritch-arena-postgresql.dump`.
- Composer validate --strict aprovado.

Nao houve deploy AWS ou criacao de recursos pagos. Nao houve verificacao visual completa nem teste manual de todos os fluxos JavaScript. O backup PostgreSQL foi gerado, mas nao foi ensaiada uma restauracao dele nesta etapa.
