# Atualização do Eldritch Arena para Laravel 13

Data: 21/09/2026. Repositório: C:\Users\JCUNME\Projetos\eldritch-arena.

## Resultado e checkpoint

Laravel 11.54.0 → 13.32.0, última versão estável disponibilizada ao Composer durante a execução. Branch de trabalho: `upgrade/laravel-13`. Checkpoint anterior: `checkpoint/pre-laravel-13-20260921`, commit `6447513`. A árvore estava limpa antes da atualização.

Ambiente validado: PHP 8.4.25 do Herd. Laravel 13 exige pelo menos PHP 8.3, mas as dependências Symfony 8.1 selecionadas exigem PHP >=8.4.1; por isso o projeto declara `^8.4.1`. Instalações devem respeitar composer.lock.

## Arquivos alterados

- composer.json: PHP ^8.2 → ^8.4.1; Laravel ^11.31 → ^13.0; Tinker ^2.9 → ^3.0; PHPUnit ^11.0.1 → ^12.0.
- composer.lock: 78 pacotes atualizados, 3 adicionados e 4 removidos; tabela completa abaixo.
- config/session.php: serialization definida explicitamente como php para manter compatibilidade com sessões existentes.
- phpunit.xml: SQLite em memória obrigatório nos testes para proteger o banco local.
- tests/Feature/UpgradeRegressionTest.php: 5 testes de regressão cobrindo páginas públicas, proteção de rotas, cadastro/login/logout, ambos os perfis, seed, torneios, inscrições, comunidade, comentários, reações e autorização de exclusão.
- README.md: versão do Laravel, requisito PHP e referência oficial atualizados.
- DOCUMENTACAO_TECNICA.md: referência da documentação atualizada para Laravel 13.
- UPGRADE_LARAVEL_13.md: este relatório.

Dependências JavaScript, controllers, models, rotas, migrations e .env não precisaram de alterações. Assets ignorados pelo Git foram recompilados em public/build; caches de configuração, rotas e views foram gerados para verificação e depois limpos.

## Compatibilidade revisada

Guias oficiais: https://laravel.com/docs/12.x/upgrade e https://laravel.com/docs/13.x/upgrade.

- Carbon 3 já estava instalado. Models usam IDs convencionais, sem dependência dos traits UUID alterados.
- Uploads já limitam tipos a jpg/jpeg/png/webp/gif, sem depender da aceitação anterior de SVG.
- Disco local e prefixos de cache/sessão já são explícitos; foram preservados.
- Formato PHP das sessões foi mantido para evitar invalidá-las ao adotar novos padrões.
- Não foram encontrados usos dos pontos alterados de upsert, helpers array_first/array_last, mergeIfMissing, referências diretas ao antigo middleware CSRF, drivers customizados ou eventos de fila alterados.
- Autenticação e gravação de dados passaram nos testes. Nenhuma incompatibilidade exigiu alteração nas regras da aplicação.

## Validação

Antes: 2 testes / 2 assertions aprovados; build Vite aprovado; 13 migrations aplicadas.
Depois: 7 testes / 49 assertions aprovados; todas as migrations recriadas em SQLite em memória pelo RefreshDatabase e seed executado nesse ambiente. No banco local, artisan migrate informou Nothing to migrate.

Composer validate --strict e check-platform-reqs aprovados. Composer não encontrou avisos de vulnerabilidades nas dependências PHP. Build Vite 6.4.3 aprovado. Configuração, rotas e views compilam. Página inicial responde HTTP 200 em http://eldritch-arena.test. git diff --check aprovado.

Limites: não houve inspeção visual manual no navegador nem teste interativo de todos os fluxos JavaScript ou upload real. Testes HTTP do Laravel não validam a proteção CSRF em condições reais de navegador. A validação de banco foi SQLite; MySQL/MariaDB e AWS não foram testados. Nenhum dado de negócio foi intencionalmente alterado no banco local; requests de verificação podem atualizar sessões. O checkpoint Git não é backup de .env, banco ou uploads ignorados.

## Retorno à versão anterior

Preserve quaisquer mudanças posteriores. Para retornar, use a branch checkpoint/pre-laravel-13-20260921, execute composer install, npm ci e npm run build, e limpe os caches de configuração, rotas e views. Como esta atualização não altera o schema local, não há migration de rollback. O trabalho desta atualização deve ser commitado ou guardado antes da troca de branch. Nenhum push foi realizado.

## Todas as alterações de pacotes

| Pacote | Antes | Depois |
| --- | --- | --- |
| brick/math | 0.14.8 | 0.19.1 |
| carbonphp/carbon-doctrine-types | 3.2.0 | 3.2.1 |
| doctrine/lexer | 3.0.1 | 3.0.2 |
| filp/whoops | 2.18.4 | 2.18.5 |
| graham-campbell/result-type | v1.1.4 | v1.2.0 |
| guzzlehttp/guzzle | 7.10.6 | 8.2.0 |
| guzzlehttp/promises | 2.4.1 | 3.0.2 |
| guzzlehttp/psr7 | 2.10.4 | 3.1.0 |
| guzzlehttp/uri-template | v1.0.6 | v2.0.1 |
| hamcrest/hamcrest-php | v2.1.1 | v3.0.0 |
| laravel/framework | v11.54.0 | v13.32.0 |
| laravel/pint | v1.29.1 | v1.32.1 |
| laravel/prompts | v0.3.18 | v0.3.24 |
| laravel/sail | v1.61.0 | v1.67.0 |
| laravel/serializable-closure | v2.0.13 | v2.0.16 |
| laravel/tinker | v2.11.1 | v3.0.2 |
| league/commonmark | 2.8.2 | 2.10.3 |
| league/flysystem | 3.34.0 | 3.36.0 |
| league/flysystem-local | 3.31.0 | 3.35.3 |
| league/mime-type-detection | 1.16.0 | 1.17.0 |
| mockery/mockery | 1.6.12 | 1.6.15 |
| monolog/monolog | 3.10.0 | 3.12.0 |
| myclabs/deep-copy | 1.13.4 | 1.14.0 |
| nesbot/carbon | 3.11.4 | 3.14.0 |
| nette/schema | v1.3.5 | v1.3.6 |
| nette/utils | v4.1.4 | v4.1.5 |
| nikic/php-parser | v5.7.0 | v5.9.0 |
| nunomaduro/collision | v8.9.4 | v8.9.5 |
| phpoption/phpoption | 1.9.5 | 1.10.0 |
| phpunit/php-code-coverage | 11.0.12 | 12.5.7 |
| phpunit/php-file-iterator | 5.1.1 | 6.0.2 |
| phpunit/php-invoker | 5.0.1 | 6.0.0 |
| phpunit/php-text-template | 4.0.1 | 5.0.0 |
| phpunit/php-timer | 7.0.1 | 8.0.0 |
| phpunit/phpunit | 11.5.55 | 12.5.35 |
| psy/psysh | v0.12.23 | v0.12.24 |
| ralouphie/getallheaders | 3.0.3 | removido |
| ramsey/uuid | 4.9.2 | 4.9.4 |
| sebastian/cli-parser | 3.0.2 | 4.2.1 |
| sebastian/code-unit | 3.0.3 | removido |
| sebastian/code-unit-reverse-lookup | 4.0.1 | removido |
| sebastian/comparator | 6.3.3 | 7.1.8 |
| sebastian/complexity | 4.0.1 | 5.0.0 |
| sebastian/diff | 6.0.2 | 7.0.1 |
| sebastian/environment | 7.2.1 | 8.1.2 |
| sebastian/exporter | 6.3.2 | 7.0.3 |
| sebastian/global-state | 7.0.2 | 8.0.3 |
| sebastian/lines-of-code | 3.0.1 | 4.0.1 |
| sebastian/object-enumerator | 6.0.1 | 7.0.0 |
| sebastian/object-reflector | 4.0.1 | 5.0.0 |
| sebastian/recursion-context | 6.0.3 | 7.0.1 |
| sebastian/type | 5.1.3 | 6.0.4 |
| sebastian/version | 5.0.2 | 6.0.0 |
| symfony/clock | v7.4.8 | v8.1.0 |
| symfony/console | v7.4.13 | v8.1.7 |
| symfony/css-selector | v7.4.9 | v8.1.6 |
| symfony/deprecation-contracts | v3.7.0 | v3.7.1 |
| symfony/error-handler | v7.4.8 | v8.1.5 |
| symfony/event-dispatcher | v7.4.9 | v8.1.5 |
| symfony/event-dispatcher-contracts | v3.7.0 | v3.7.1 |
| symfony/finder | v7.4.8 | v8.1.7 |
| symfony/http-foundation | v7.4.13 | v8.1.7 |
| symfony/http-kernel | v7.4.13 | v8.1.7 |
| symfony/mailer | v7.4.12 | v8.1.7 |
| symfony/mime | v7.4.13 | v8.1.7 |
| symfony/polyfill-intl-grapheme | v1.38.1 | v1.41.0 |
| symfony/polyfill-intl-idn | v1.38.1 | v1.42.0 |
| symfony/polyfill-intl-normalizer | v1.38.0 | v1.42.0 |
| symfony/polyfill-mbstring | v1.38.1 | v1.38.2 |
| symfony/polyfill-php82 | — | v1.38.1 |
| symfony/polyfill-php83 | v1.38.1 | removido |
| symfony/polyfill-php84 | — | v1.38.1 |
| symfony/polyfill-php85 | v1.38.1 | v1.41.0 |
| symfony/polyfill-php86 | — | v1.41.0 |
| symfony/process | v7.4.13 | v8.1.7 |
| symfony/routing | v7.4.13 | v8.1.6 |
| symfony/service-contracts | v3.7.0 | v3.7.3 |
| symfony/string | v7.4.13 | v8.1.7 |
| symfony/translation | v7.4.10 | v8.1.5 |
| symfony/translation-contracts | v3.7.0 | v3.7.1 |
| symfony/uid | v7.4.9 | v8.1.5 |
| symfony/var-dumper | v7.4.8 | v8.1.7 |
| symfony/yaml | v7.4.13 | v8.1.6 |
| theseer/tokenizer | 1.3.1 | 2.0.1 |
| vlucas/phpdotenv | v5.6.3 | v5.7.0 |
