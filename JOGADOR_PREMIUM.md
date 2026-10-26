# Jogador gratuito e Arena Plus

## Onde acessar

- Meus decks: https://18.224.27.22.sslip.io/meus-decks
- Minhas análises (Plus): https://18.224.27.22.sslip.io/meus-decks/analises
- Exemplo para a mostra: https://18.224.27.22.sslip.io/meus-decks/exemplo

As páginas exigem login. As rotas equivalentes funcionam no endereço local `http://eldritch-arena.test`. A prévia tem 24 partidas fictícias, recalculadas em memória; não grava resultados ou decks na conta.

## Comparação de recursos

| Recurso | Gratuito | Plus |
| --- | --- | --- |
| Cadastro de decks por jogo, formato e arquétipo | Sim | Sim |
| Histórico privado de partidas | Sim | Sim |
| Vitória, derrota, empate, data e contexto | Sim | Sim |
| Anotações de treino e correção de resultados | Sim | Sim |
| Remover/restaurar partidas e arquivar/reativar decks | Sim | Sim |
| Taxa de vitória por deck | Não | Sim |
| Confrontos contra arquétipos adversários ou composições de mesa | Não | Sim |
| Filtros por deck, jogo, formato, período, jogadores e contexto | Não | Sim |
| Evolução mensal, últimas 10 partidas e sequência atual | Não | Sim |
| Exportação CSV do recorte | Não | Sim |
| Visualização do exemplo fictício | Sim | Sim |

O acesso antecipado Plus continua gratuito; não existe cobrança real. O projeto atualmente usa um único indicador de Plus ativo na conta, que acompanha a troca entre perfil jogador e organizador. Os nomes históricos `player_premium` e `loja_premium` não são assinaturas independentes ou cumulativas. Desativar Plus bloqueia análise e exportação, mas mantém decks, partidas, notas e edição básica.

## Registro e interpretação

Cadastre o nome do deck e selecione jogo/formato. Depois de jogar, registre uma partida completa, o resultado, a data, o número total de jogadores e o contexto (casual/treino ou torneio). Para não distorcer a taxa, não misture jogos individuais e séries melhor de três no mesmo histórico.

Em duelos, informe o arquétipo do adversário. Em multiplayer, informe a composição da mesa; o resultado pertence à mesa inteira, não a uma suposta vitória individual contra cada deck presente. O filtro padrão mostra apenas mesas de dois jogadores dos últimos 90 dias. Para Commander ou outra mesa com mais participantes, escolha o número correto no registro e no filtro.

**Taxa de vitória = vitórias / total de partidas × 100**, arredondada a uma casa decimal. Empates fazem parte do total. Sem partidas, aparece um traço, não uma taxa fictícia de zero. Exemplo: 2 vitórias, 1 derrota e 1 empate resultam em 50%.

Confrontos são agrupados pelo identificador do próprio deck e nome normalizado do adversário (espaços e caixa). Grafias, sinônimos e acentos diferentes não são automaticamente reconciliados. Corrija o registro para padronizar nomes. Decks diferentes não são combinados mesmo que tenham nomes iguais.

Menos de cinco registros é sinalizado como amostra pequena. Isso é uma advertência prática, não uma garantia estatística de confiabilidade quando há cinco ou mais registros. A taxa representa o histórico pessoal; não é ranking oficial, previsão de vitória ou prova de superioridade de um deck.

Evolução mensal e últimas dez partidas respeitam todos os filtros. Meses sem registros são omitidos. A sequência atual termina na primeira derrota ou empate; ordena pela data e, dentro do mesmo dia, pela ordem de cadastro. A data é registrada sem hora, usando o dia corrente de Rio Branco para impedir datas futuras.

## Histórico, versões e privacidade

- Depois que houver uma partida (mesmo removida), jogo e formato do deck não podem mudar. Isso preserva a classificação dos resultados.
- Para comparar uma alteração importante da lista, cadastre outro deck com um nome de versão. Não existe versionamento automático de cartas ou validação de legalidade nesta etapa.
- Arquivar o deck impede novos registros, mas preserva suas partidas nas análises.
- Remover uma partida é reversível: ela sai dos cálculos e fica em **Registros removidos**, com opção de restaurar.
- Todas as consultas e alterações verificam o proprietário. Admin não recebe acesso automático ao histórico privado de outros jogadores.
- Dados são declarados manualmente. Não há importação do Arena/duelos, sincronização do contador de vida, ranking competitivo ou validação por organizadores.
- Não foram criadas partidas fictícias nas contas reais. A prévia demonstrativa usa dados separados em memória.

## Roteiro para a mostra

1. Abra a prévia com dados fictícios para mostrar a diferença visual e explicar os números.
2. Compare Burn e Controle Azorius e, em seguida, os confrontos contra Tron e Murktide.
3. Mostre a contagem ao lado da porcentagem e o aviso de amostra pequena.
4. Mostre a evolução mensal e as últimas dez partidas.
5. Abra **Arena Plus** para comparar gratuito/Premium. Explique que o registro e a posse dos dados continuam gratuitos; o diferencial é a análise e a exportação.
6. Se quiser registrar uma partida ao vivo, use uma conta exclusiva de demonstração e identifique o deck como exemplo. Não apresente o resultado fictício como desempenho pessoal real.

## Implementação e validação

`player_decks` guarda proprietário, nome, jogo, formato, arquétipo, notas e arquivamento. `player_matches` guarda resultado, adversário normalizado, data, quantidade de jogadores, contexto, notas e remoção reversível. O identificador único de envio protege contra duplo clique, e o registro bloqueia a linha do deck para coordenar com arquivamento e troca de formato.

`PlayerDeckController` valida e autoriza os fluxos; `PlayerInsights` calcula os indicadores sobre registros já filtrados do proprietário. Exportação CSV aplica os mesmos filtros e neutraliza fórmulas em textos fornecidos pelo usuário. Análises e exportação exigem Plus no servidor.

Validação de 26/10/2026: 45 testes PHP / 450 asserções em SQLite e PostgreSQL, build Vite e compilação Blade concluídos. Verificação no navegador local da seleção dinâmica de formatos e da prévia completa, sem inserir partidas na conta do usuário. A suíte cobre taxas com empates, dados vazios, normalização, multiplayer separado, privacidade, filtros, correções, remoção/restauração, arquivamento, envio duplicado e cancelamento do Plus sem perda de dados.

## Publicação

Publicado na AWS em 26/10/2026. A migration aditiva foi executada antes da troca da aplicação. Backup privado de código e banco em `backups/20261026-player-stats`; imagem anterior preservada como `eldritch-arena-app:pre-player-stats-20261026`. PHP permanece em 8.4.27.

Verificações por HTTPS passaram para os perfis de teste jogador e lojista: área de decks, formulário, prévia de análise, página de planos, acesso às análises conforme o plano, exportação e assets. Nenhum deck ou partida foi inserido nas contas durante esses testes públicos. Os testes locais cobriram as operações de escrita e os bloqueios de acesso no gratuito.

O disco da VPS permanece com aproximadamente 354 MB livres (95% ocupado); o crescimento da infraestrutura continua pendente.
