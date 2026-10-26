# Reports de torneios — etapa 1

Atualização local de 26/10/2026. Esta etapa não foi implantada na AWS.

## Acessar

- `/reports`: lista pública, filtros por jogo, formato, cidade exata e UF em maiúsculas.
- `/reports/exemplo`: demonstração fictícia de quatro jogadores e duas rodadas. Não grava eventos, partidas ou contas no banco.
- Torneios → abrir evento próprio → **Apurar resultados**.

## Fluxo do organizador

1. Depois do horário do evento, selecionar ao menos dois inscritos confirmados que compareceram. A lista fica fixa; faltantes não entram no denominador do meta. Confirmar que o evento usa duelos 1×1.
2. Informar cidade/UF opcionais, apelidos públicos autorizados e arquétipos. Os nomes das contas aparecem somente na tela privada do organizador. O padrão público é “Jogador N”.
3. Parear manualmente cada rodada. Um participante só aparece uma vez na mesma rodada. É permitido um bye com número ímpar de presentes. A próxima rodada exige todas as anteriores completas. Limites: 256 presentes e 15 rodadas.
4. Registrar vitória de A/B ou empate, com anotação para auditoria. Corrigir um pareamento anulando-o com motivo e criando o correto; o histórico permanece.
5. Conferir a prévia e publicar. Nenhuma partida pode ficar pendente e todos os presentes precisam estar pareados em cada rodada.
6. Para corrigir report publicado, informar motivo e **retirar do ar/reabrir**. A página pública retorna 404 até republicação. O report anterior não permanece visível com dados desatualizados.

Após iniciar a apuração, não é permitido alterar o evento, cancelar o evento ou excluir inscrições. Isso preserva o vínculo com os resultados; esta etapa não oferece edição da lista de presentes nem cancelamento após apuração.

## Como interpretar

- Vitória = 3 pontos; empate = 1; derrota = 0; bye = 3.
- Taxa de vitória = vitórias / (vitórias + empates + derrotas). Bye não conta como duelo nem como vitória. Sem duelos, aparece “—”.
- Empates em pontos compartilham a posição (1, 2, 2, 4). A ordenação alfabética dentro do empate serve só à exibição.
- Participação de deck = presentes com aquele arquétipo / total de presentes. Arquétipos vazios entram como “Não informado”. Espaços/capitalização são normalizados para agrupamento.
- Dados declarados pelo organizador, sem homologação de federação/editora ou confirmação independente pelos jogadores.
- Representa os eventos registrados na Arena, não a totalidade do meta regional. Não mistura dados pessoais de treino de `PlayerMatch` com resultados competitivos.

## Implementação e segurança

A migração `2026_10_26_000003_add_tournament_reports` acrescenta estado privado de apuração, revisão, início e snapshot público em `tournaments`, além da tabela privada `tournament_report_audits`.

`TournamentReportService` centraliza invariantes e cálculos. Toda alteração acontece em transação com bloqueio do torneio e checagem de revisão, impedindo sobrescrita por abas antigas e pareamentos concorrentes. O estado é armazenado em JSON; o snapshot público contém somente informações necessárias ao report, sem IDs de usuários, e-mails, nomes das contas ou endereço completo do evento. A auditoria registra autor, ação, motivo, revisão e entrada validada. Não exponha essa tabela publicamente.

Somente o organizador do evento pode apurar. Publicação não exige Premium. O navegador usa formulários com CSRF, validação no servidor e limite de requisições. Consultas públicas exigem publicação e evento não cancelado.

Atualização local: `php artisan migrate --force`, `npm run build` e `php artisan view:clear`. Não usar `migrate:fresh` no banco real.

## Validação

Suite em SQLite e PostgreSQL de testes; cobre publicação, empates, meta, privacidade, autorização, isolamento de participantes, revisão desatualizada, presença, dupla alocação, byes, rodadas incompletas, retirada/republicação e filtros. Build Vite e revisão visual do exemplo local.

## Próximas etapas

Ligas e temporadas, ranking mensal com regulamento explícito, desempates por modalidade, gestão de desistências/presença após início, pareamentos automáticos, decklists completas e comparações agregadas entre eventos. Nenhum desses recursos é anunciado como pronto nesta etapa. Não é um simulador automático de duelos.
