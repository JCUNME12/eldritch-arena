# Mostra da faculdade — roteiro demonstrável

## Acesso e objetivo

- Site: https://18.224.27.22.sslip.io/mostra
- Local: http://eldritch-arena.test/mostra
- Entre com a conta do apresentador já cadastrada. As lojas são vinculadas a essa conta; não existe uma senha pública compartilhada.
- **Mesa Inicial** começa gratuita. **Arcana Prime** começa Plus. Cada uma tem estoque separado com Sol Ring, Lightning Bolt e Mago Negro.
- Todo o módulo está identificado como demonstração: nenhum pagamento, cartão, Pix ou boleto real é processado.

## Roteiro de 5–8 minutos

1. Abra a comparação em `/mostra`. Explique a proposta gratuita e Plus. Os R$ 49,90/mês e taxas de 5%/2% são exemplos de modelo de negócio, não preços comerciais definidos nem taxas de um provedor.
2. Abra Mesa Inicial. Mostre unidades, alerta de reposição e a vitrine. Escolha Sol Ring e crie um pedido Pix de uma unidade. O pedido fica pendente e o saldo continua em 12 (em uma demo recém-preparada).
3. Clique em **Simular aprovação**: o saldo passa a 11 e o histórico registra a confirmação. Volte ao painel para mostrar o pedido aprovado.
4. Reabra o pedido e clique em **Simular estorno integral**: o saldo volta a 12. O histórico é preservado. Explique que estornos reais dependeriam do provedor e da política de devolução; aqui a devolução física é presumida para demonstrar o estoque.
5. Crie outro pedido por cartão e simule recusa. O saldo não muda. Com boleto, deixe pendente para representar a espera por compensação e depois cancele. Nenhum QR, chave Pix ou boleto pagável é gerado.
6. Abra Arcana Prime. Aprove uma venda e mostre bruto, taxa, líquido e margem estimada; exporte CSV. O relatório é protegido no servidor e a loja gratuita não consegue acessá-lo por URL.
7. Na Mesa Inicial, escolha **Experimentar assinatura Plus**. Crie a assinatura de teste e mostre que o pedido pendente não libera os recursos. Aprove e volte ao painel: análise e exportação aparecem, e novos pedidos usam a taxa ilustrativa de 2%.
8. Estorne essa assinatura para deixar Mesa Inicial gratuita novamente. A Arcana Prime permanece Plus. Feche mostrando o painel real em `/loja`, onde continuam cadastro, entradas/saídas, anúncios e histórico; lojistas Plus também possuem projeção de acervo e CSV de estoque.

## Como explicar a influência dos pagamentos

| Evento simulado | Estoque | Plano e indicadores |
| --- | --- | --- |
| Pedido criado / pendente | Não reserva nem baixa | Não entra nos totais aprovados |
| Venda aprovada | Baixa uma vez, com validação do saldo | Entra nos totais de vendas |
| Recusa ou cancelamento | Sem alteração | Não gera receita simulada |
| Estorno integral de venda | Devolve as unidades uma vez | Retira a venda dos totais aprovados |
| Assinatura aprovada | Sem alteração | Ativa Plus na loja demo |
| Assinatura estornada | Sem alteração | Retorna a loja demo ao gratuito |

Os valores são calculados no servidor em centavos. Preço, custo e taxa ficam registrados no pedido: mudar o plano não recalcula pedidos antigos. Taxas são arredondadas para o centavo mais próximo, com metade arredondada para cima.

O líquido é bruto menos taxa ilustrativa. A margem também subtrai o custo cadastrado na criação do pedido. Isso não é lucro contábil e não desconta assinatura, impostos, frete ou despesas. Assinaturas não são somadas às vendas de cartas da loja. O CSV mantém os valores originais e informa o estado; o líquido de operações não aprovadas é zero.

## Separação entre demonstração e produto real

- A mostra usa `stores.is_demo` e uma conta proprietária fictícia por loja, com senha aleatória não distribuída. O apresentador acessa as demos com seu próprio login, sem impersonar essas contas.
- A autorização exige loja demo e apresentador, proprietário ou administrador. A lista inicial mostra somente lojas vinculadas à conta; um admin pode inspecionar uma demo por sua rota.
- Os produtos fictícios não são publicados no marketplace. Estoque e plano de lojas reais não são alterados pela simulação.
- A assinatura em `/mostra` é demonstrativa. O acesso antecipado em `/premium` continua gratuito; a apresentação distingue essas duas situações.
- O painel real ganhou projeção de acervo e CSV de estoque para Plus. O estoque básico e o histórico permanecem gratuitos.
- Ainda não há gateway, webhook de provedor, recorrência, expiração automática de assinatura, parcelamento, entrega, nota fiscal ou estorno parcial. Os botões do apresentador representam cenários, não notificações bancárias reais.
- Não há reserva de estoque pendente. A aprovação confere o saldo novamente; se faltar, apresenta erro e mantém o pedido pendente.

## Preparação e manutenção

Depois de backup e migration, prepare as lojas para uma conta existente:

```sh
php artisan migrate --force
php artisan arena:showcase email-do-apresentador
```

O comando é idempotente: não redefine saldo, plano, senha ou histórico de demos existentes. Se o usuário entrar com outra conta, suas lojas não aparecem; prepare um conjunto próprio para essa conta ou use o login original. Não execute o seeder geral em produção.

O erro `column is_demo does not exist` significa que o código novo foi publicado antes da migration. A implantação deve executar a migration aditiva antes de substituir o serviço da aplicação. Não é necessário recriar ou apagar o banco.

Para repetir a apresentação, use os estornos e cancelamentos da própria interface; não apague históricos. O retorno ao gratuito é feito estornando a assinatura demo aprovada da Mesa Inicial.

## Validações de 26/10/2026

- 38 testes PHP / 351 asserções em SQLite e em PostgreSQL dedicado de testes.
- 33 testes JavaScript das ferramentas de mesa.
- Build Vite concluído.
- Navegador local: compra Pix, aprovação, baixa 12→11, estorno 11→12, assinatura por cartão, liberação Plus e retorno ao gratuito.
- Testes automatizados incluem autorização, isolamento de lojas, submissão duplicada, estoque insuficiente, estados terminais, taxas, recusa, cancelamento, assinatura duplicada e exportações.

Os testes não constituem homologação de um provedor de pagamentos, pois não há integração externa nesta etapa.

## Publicação na AWS

Versão publicada em 26/10/2026 sobre a imagem PHP 8.4.27 corrigida, com migration aplicada antes da troca da aplicação. Backup privado do banco e código em `backups/20261026-showcase`; imagem anterior preservada como `eldritch-arena-app:pre-showcase-20261026`. A atualização incremental copiou somente código e assets, sem recompilar o PHP ou alterar o PostgreSQL.

Pelo HTTPS público, foram verificados os logins e páginas existentes, compra Pix, aprovação repetida sem duplicação, estoque 12→11→12 após estorno, recusa de cartão, cancelamento de boleto, totais Plus e assinatura que libera/revoga o relatório. Os cenários deixaram histórico nas lojas fictícias de teste; saldo e plano inicial foram restaurados pelos estornos. O conjunto do apresentador permaneceu pronto para começar.

A conta genérica de lojista usada no teste remoto ainda não possui loja real, então `/loja` exibe o cadastro e o relatório retorna 404. A análise e o CSV de estoque real foram validados nos testes locais com lojas preparadas em SQLite e PostgreSQL, incluindo isolamento entre proprietários.

Após publicar, restavam aproximadamente 356 MB livres no disco de 6,8 GB da VPS (95% ocupado). A ampliação do disco segue necessária para crescimento e futuras compilações maiores.
