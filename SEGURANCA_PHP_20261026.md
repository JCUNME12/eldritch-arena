# Atualização de segurança do PHP — 26/10/2026

## Escopo

Atualização de PHP 8.4.25 para 8.4.27 para corrigir CVE-2026-109618 e CVE-2026-109619. Também foi aplicada a orientação de 23/10: `session.upload_progress.enabled=0`, como proteção adicional enquanto o projeto PHP prepara o próximo bugfix relacionado ao progresso de upload de sessão (CVE-2026-115560).

Fontes oficiais:
- https://www.php.net/archive/2026.php#2026-10-21-5
- https://www.php.net/archive/2026.php (avisos de 23/10 e 26/10)
- https://www.php.net/downloads.php?os=windows&version=8.4&installation=zip

## Windows / Herd

Pacote oficial NTS x64 VS17 `php-8.4.27-1-nts-Win32-vs17-x64.zip`, revisão de 26/10 com Brotli 1.2.1. SHA-256 conferido antes de extrair:

```text
dbae154a0a89791f36c3d48e68bae797878333e7d6cb5721e74617de59b63ab7
```

O executável informa 8.4.27; o sufixo `-1` identifica o pacote Windows, não uma versão diferente do runtime. A consulta de cURL confirmou `brotli/1.2.1`.

O diretório PHP 8.4 gerenciado pelo Herd recebeu os binários oficiais, preservando o `php.ini` e as extensões Herd/Redis/MongoDB. A instalação anterior foi guardada, fora do Git, em `~/.config/herd/bin/php84-backup-20261026`. Não reative essa versão vulnerável para uso normal. A atualização foi manual; futuras atualizações pelo Herd devem manter a mitigação e ser verificadas após instalação.

CLI e FastCGI: 8.4.27. Herd iniciado com os processos apontando para a pasta atualizada. Login local respondeu HTTP 200. Testes PHP passaram em SQLite e PostgreSQL: 28 testes / 230 asserções em cada execução.

## VPS / Docker

Base oficial fixada em versão e digest:

```text
php:8.4.27-apache-bookworm@sha256:e653ff90d7f6e2e6f81eddb07c864f1eae224ccbf5143f82a44f25e2f110e320
```

As extensões pdo_pgsql, intl, zip, mbstring, bcmath e opcache são compiladas a partir do PHP corrigido. A primeira tentativa não coube no disco. O contêiner temporário malsucedido e o cache de build foram removidos; banco, uploads e serviços existentes foram preservados. A compilação seguinte exclui somente os diretórios de testes das fontes do PHP para reduzir uso de disco. Isso não altera as extensões instaladas nem os testes da aplicação.

`deploy/aws/Dockerfile` registra o Dockerfile desta instalação. Ele requer o contexto de implantação existente na VPS, incluindo vendor, assets, apache.conf e start.sh. Não deve ser executado sobre a raiz local sem preparar esses arquivos e um `.dockerignore` que exclua .env, .git, backups, dumps, chaves e logs. Não é um pipeline completo de implantação.

Backup do banco e do Dockerfile anterior: diretório privado `backups/20261026` da instalação. A imagem anterior foi marcada `eldritch-arena-app:pre-security-20261026` para recuperação emergencial, mas contém a vulnerabilidade e não deve ser usada como destino permanente de rollback. Nenhum dump, senha ou chave foi incluído no Git.

O patch não requer migrations ou alteração dos dados. Não equivale a investigação de comprometimento anterior, nem comprova ausência de outras vulnerabilidades.

## Verificação após publicação

A imagem corrigida foi validada antes da troca e somente o serviço app foi recriado. PHP 8.4.27 confirmado no contêiner; `session.upload_progress.enabled` está Off. A consulta de migrations confirmou as 16 migrations existentes aplicadas, sem executar mudanças no banco.

No endereço HTTPS público, os logins de demonstração de jogador e lojista passaram, com nove páginas autenticadas e assets verificados para cada perfil. Também passaram as verificações de /loja, /loja/produtos/novo, /esquemas, catálogo JSON de esquemas, /up, texto UTF-8 no dashboard e regra do service worker para páginas autenticadas. Esses testes de navegação não substituem testes completos de todas as operações de escrita.

Após a publicação, o disco da VPS tinha 358 MB livres de 6,8 GB (95% ocupado). É necessário ampliar a capacidade antes de novas compilações ou crescimento significativo dos dados. Nenhum volume de banco ou upload foi removido para liberar espaço.
