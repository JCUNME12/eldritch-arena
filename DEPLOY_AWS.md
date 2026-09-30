# Publicação na AWS

## Implantação de 30/09/2026

Endereço: https://18.224.27.22.sslip.io/. A aplicação roda em EC2 com Docker Compose: PHP 8.4/Apache, PostgreSQL 18 e Caddy para HTTPS. Banco, uploads e certificados usam volumes persistentes. Não há pipeline automático de publicação.

Foi publicada a reformulação da branch `feature/arena-professional`, incluindo contador e formatos, esquemas, loja/estoque, painel administrativo e correção de acentuação no dashboard. Foram aplicadas as três migrations de 28/09 (formato/cancelamento de torneios, revisão de textos demonstrativos e estoque/admin). A permissão administrativa solicitada foi aplicada diretamente no banco da VPS, sem trocar senha ou perfil; não é distribuída pelo Git.

## Backup e preservação

Antes da atualização foram criados, no diretório privado `backups/20260930` da instalação, um dump PostgreSQL e uma cópia dos arquivos substituídos. A imagem anterior foi preservada como `eldritch-arena-app:pre-20260930`. O diretório de backups foi excluído do contexto Docker. Dumps, chaves, credenciais e `.env` não pertencem ao repositório.

O banco de produção foi preservado: não houve reset, importação do banco local ou execução de seeders. As dependências Composer tinham o mesmo hash de lock nos dois ambientes e foram reutilizadas. A imagem foi atualizada a partir da anterior com código e assets, mantendo a configuração operacional.

## Configuração operacional

Os arquivos Docker Compose, Dockerfile, Caddyfile e o script de inicialização atualmente residem na instalação da VPS e ainda não estão versionados neste repositório. Portanto, clonar o código não reproduz sozinho a implantação. O `bootstrap/app.php` da VPS mantém a configuração de proxies confiáveis, ausente no arquivo local; não sobrescreva esse ajuste sem revisar a topologia de rede e o HTTPS.

O endereço usa o IP público no domínio sslip.io. Uma mudança de IP exige atualizar o endereço/configuração. O disco tinha aproximadamente 1,1 GB livres após a publicação; confira espaço antes de novos builds e backups.

## Próximas publicações

1. Conferir branch, alterações, testes PHP/JS e build; nunca copiar `.env` local, banco ou credenciais para o pacote.
2. Conferir espaço, backups e configuração da VPS, preservando volumes e imagens necessárias à recuperação.
3. Produzir uma imagem com as dependências do lock e assets correspondentes ao código; revisar migrations antes da aplicação.
4. Aplicar migrations não destrutivas com `php artisan migrate --force` no ambiente correto e recriar somente a aplicação. Coordenar manutenção se houver mudanças incompatíveis.
5. Conferir saúde, login, rotas restritas, assets HTTPS, estoque, esquemas e logs. Publicar no GitHub é uma operação separada.

Não execute `migrate:fresh`, seeders indiscriminados ou remoção de volumes em produção. Para recuperação, avalie primeiro restaurar a imagem anterior mantendo migrations aditivas. Restaurar um dump remove alterações posteriores ao backup e exige decisão explícita; não é rollback automático.

## Validação realizada

Login de jogador e lojista e nove páginas autenticadas por perfil responderam corretamente. Também foram conferidos `/loja`, `/loja/produtos/novo`, `/esquemas`, catálogo JSON, `/up`, assets HTTPS, a frase “Valor previsto de inscrições” e o service worker atualizado. PostgreSQL permaneceu saudável.

O build desta sessão foi bloqueado pelo sandbox local; foram usados os assets aprovados em 28/09, sem mudanças posteriores em CSS/JS. Essa limitação não deve ser confundida com um novo build aprovado em 30/09. A atualização não adiciona pagamentos, reservas ou baixa automática por vendas.
