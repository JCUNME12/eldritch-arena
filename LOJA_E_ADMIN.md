# Loja, estoque e administração

Implementação local de 28/09/2026. A AWS não foi alterada.

## Fluxos disponíveis

- Minha loja (`/loja`): perfil público da loja, nome e e-mail de contato, cadastro e edição de cartas por SKU, edição, condição e raridade.
- Estoque: saldo inicial, entradas/saídas com motivo, quantidade mínima, busca por nome/SKU, filtros de estoque baixo, sem estoque e arquivados. Custo unitário e preço de venda separados. Custo total usa quantidade atual × custo unitário atual; não é contabilidade, lucro nem fluxo de caixa.
- Movimentações guardam responsável, data, motivo, saldo anterior e final. Não há exclusão de histórico pela interface. Correções exigem uma nova movimentação com motivo.
- Publicar/pause um produto no marketplace. A publicação gera um único anúncio vinculado ao item. Alterações de descrição/preço da loja são refletidas nesse anúncio. O saldo zero retira o anúncio das listagens, dos destaques e do acesso de compradores. Reposição reativa a disponibilidade se o anúncio não foi pausado. Um produto arquivado tem anúncio pausado e só pode ser arquivado sem saldo.
- Anúncios antigos/avulsos continuam funcionando; não são convertidos automaticamente em estoque, pois não existe quantidade conhecida para eles.
- Administração (`/admin`): acesso restrito, visão de contas, lojas, anúncios disponíveis e torneios, busca de contas por nome/e-mail. Este primeiro painel é de consulta; não inclui moderação, exclusão de usuários, pagamentos ou concessão de privilégios pela web.

## Permissões e integridade

O perfil existente `organizer` representa loja/organizador. `is_admin` é um privilégio independente e não participa dos campos de atribuição em massa. Cadastro e troca de perfil só aceitam jogador/organizador e não permitem conceder administração. Um administrador pode usar sua própria área de loja mesmo com perfil jogador, mas não recebe acesso de escrita ao estoque de terceiros por URLs.

Todos os endpoints da loja exigem autenticação e perfil autorizado. Leituras e alterações de produtos conferem o proprietário da loja. Custos e histórico não são expostos no marketplace. As quantidades são alteradas somente por movimentações validadas em transação, com bloqueio da linha do produto. Cada formulário possui identificador único para impedir aplicação repetida da mesma movimentação. SKU é único por loja, normalizado em maiúsculas. Valores monetários usam colunas decimais e validação de até duas casas.

Concessão administrativa exige acesso ao console do servidor e conta já cadastrada:

```text
php artisan arena:admin email-da-conta
php artisan arena:admin email-da-conta --revoke
```

O comando preserva senha e tipo de perfil e registra concessões/revogações em `admin_access_logs`. Nenhum e-mail pessoal ou senha é codificado no projeto. A mudança de permissão em um banco local não é replicada pelo GitHub nem altera o banco de produção.

## Banco e operação

A migration `2026_09_28_000003_create_store_inventory` adiciona `users.is_admin`, lojas, produtos, movimentações, ligação ao marketplace e auditoria de acesso. Não modifica anúncios anteriores nem inventa quantidades. Backup privado do banco local foi criado antes de aplicar a migration; não foi incluído no Git.

As alterações são salvas no GitHub, mas dependem de execução de migrations e build no ambiente onde forem publicadas. Evite rollback das tabelas após uso real, pois remove os registros de estoque: prefira correção por nova migration e backup.

## Validação e limites desta etapa

- 28 testes Laravel, 230 asserções, aprovados em SQLite e PostgreSQL.
- Cobertura: isolamento entre lojas, bloqueio de jogador/visitante, acesso admin independente do perfil, cadastro/troca de perfil forjados, auditoria, saldo insuficiente, reenvio idempotente, SKU duplicado, precisão monetária, publicação/pausa/reposição e arquivo com histórico preservado.
- Build Vite e compilação Blade aprovados. Revisão no navegador com conta de loja de demonstração: criação, movimentação, histórico, alertas e tela móvel.
- A conta de demonstração local tem uma loja e um produto explicitamente identificados como demonstração, sem publicação no marketplace. Não foram criados produtos na conta pessoal do usuário.
- Não há checkout, reserva de unidades, baixa automática por venda online, gestão fiscal, importação de planilhas, funcionários por loja ou integração contábil. O lojista registra a saída após confirmar a venda fora da plataforma.
