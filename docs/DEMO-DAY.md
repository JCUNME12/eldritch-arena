# Demo Day — execução local

Atualizado em 11/11/2026. A VPS permanece desligada.

## Abrir

Execute `INICIAR_DEMO.cmd` na raiz do projeto. Ele inicia o PostgreSQL local e o servidor em http://127.0.0.1:8000 e abre `/mostra`. O processo não publica o site na internet.

As credenciais de apresentação ficam somente em `.local/demo-day-access.txt`, ignorado pelo Git. Não incluir senhas em slides ou commits.

## Roteiro

1. Abrir `/mostra`: comparar Mesa Inicial (gratuita) com Arcana Prime (Plus).
2. Adicionar produtos das duas vitrines ao carrinho. Alterar quantidade, remover e finalizar.
3. Escolher Pix, cartão ou boleto **simulado**. Não há cobrança, QR pagável, coleta de cartão, entrega ou frete real.
4. Aprovar o pedido e conferir estoque e resultados nas lojas. Estornar integralmente e conferir a reposição.
5. Demonstrar também recusa e cancelamento: não alteram estoque.
6. Em `/loja`, mostrar cadastro de produto, movimentos e publicação de anúncio.
7. Em `/meus-decks`, mostrar os decks e partidas fictícias. Em `/premium`, ativar/cancelar o Plus demonstrativo e comparar análises.
8. Mostrar torneios, comunidade e reports. Reports de exemplo são identificados; não representam torneios reais.
9. Na mesa, escolher Magic ou Yu-Gi-Oh!, formato, jogadores, dados e esquemas do Archenemy.

## Carrinho

O marketplace exibe ofertas demo com botão **Comprar**, busca e paginação. O catálogo expandido contém 23 cartas em cada uma das duas lojas (46 ofertas). Para acrescentar as 20 cartas extras por loja em outro ambiente, execute `php artisan arena:demo-catalog EMAIL_DO_APRESENTADOR` após `arena:showcase`. O comando é repetível e não redefine estoque ou preços existentes. Os valores e metadados são ilustrativos.

Carrinho em sessão separado por usuário; até 30 produtos e 99 unidades por produto, limitado pelo estoque. Aceita produtos das lojas demo autorizadas. Anúncios comuns continuam com negociação direta.

Preços, custos e taxas vêm do servidor e são preservados no pedido. Pendentes não reservam estoque. Aprovação e estorno de vários produtos são atômicos: se qualquer linha falhar, nenhuma é aplicada. Reenvios não duplicam o pedido nem a movimentação. Cada loja recebe suas linhas no painel e relatório existentes. Alterações de estado devem ocorrer no pedido completo, não em linhas isoladas.

## Verificação

- PHPUnit: 55 testes / 615 verificações, tanto em SQLite isolado quanto PostgreSQL de testes.
- Cobertura: autenticação, permissões, torneios/inscrições, reports, marketplace, comunidade, estoque, planos, decks/estatísticas, pagamentos e carrinho.
- JavaScript: 34 testes de contador e esquemas.
- Build Vite concluído; migração do carrinho aplicada após backup privado.
- Navegador local: login, painéis das duas lojas, compra com duas lojas, quantidades, aprovação e estorno confirmados.

Testes não garantem ausência absoluta de falhas. Imagens remotas de cartas/esquemas dependem de internet. Pagamentos são uma demonstração, não uma integração com adquirente. Backups e dados privados permanecem fora do Git, em `.local/backups/demo-day-20261111/`.
