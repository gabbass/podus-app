# Mapa do Código do PODUS App

Este documento resume a estrutura atual do repositório e aponta onde
cada responsabilidade foi implementada. Use-o como guia rápido para
navegar pelo código e localizar pontos de extensão.

## Visão geral por camadas

### Núcleo Laravel
- `app/Http/Controllers` concentra os controladores HTTP. A camada API
  moderna fica em `Api/`, com controladores como
  `ExamController` e `PlanningApiController` respondendo a requisições
  JSON usando o helper `App\Http\JsonResponse`.
- `app/Http/Request` encapsula o acesso a parâmetros e arquivos e é
  compartilhado entre controllers legados e modernos.
- Middlewares e guards legados estão em `app/Auth/Middleware` e
  `app/Auth/LegacySessionGuard.php`, permitindo reutilizar sessões e
  perfis (`App\Auth\Profiles`) herdados do sistema antigo.

### Serviços e integrações
- `app/Services/ExamAutoGrader.php` orquestra a leitura OMR e devolve o
  payload normalizado consumido pelos jobs de processamento.
- `app/Services/MoodleClient.php` encapsula chamadas REST para o Moodle;
  seus métodos são utilizados pelos jobs `SyncExamWithMoodle` e
  `SyncGradeWithMoodle`.
- `app/Services/RoomReservationService.php` centraliza as regras de
  agendamento de salas consumidas por `PlanningApiController`.

### Jobs e eventos
- `app/Jobs/ProcessExamScan.php` processa digitalizações de provas,
  grava itens em `ExamScanItem`, atualiza o status do scan e dispara
  `App\Events\GradeReleased` através do `App\Support\EventDispatcher`.
- `app/Jobs/SyncExamWithMoodle.php` e `app/Jobs/SyncGradeWithMoodle.php`
  sincronizam avaliações e notas com o Moodle, utilizando o cliente
  dedicado.
- Eventos ficam em `app/Events`, e ouvintes relacionados vivem em
  `app/Listeners`.

### Modelos e acesso a dados
- Modelos Eloquent residem em `app/Models`. Destaques:
  - `Exam`, `ExamQuestion` e `ExamScan` cuidam da gestão das provas.
  - `Room` e `RoomReservation` sustentam a reserva de salas.
  - `User` abstrai o usuário autenticado e expõe IDs legados.
- Métodos estáticos como `Exam::list` e `ExamScan::markProcessing`
  refletem regras herdadas do banco legado.
- Helpers de banco legado (`App\Support\LegacyDatabase` e
  `App\Support\LegacySchema`) expõem consultas PDO diretas onde o ORM
  não é suficiente.

### Suporte e infraestrutura
- `app/Support` agrupa utilitários compartilhados: manipulação de
  sessões (`Session/SessionManager.php`), wrappers de resposta (`ResponseFactory`)
  e funções globais (`helpers.php`).
- Configurações ficam em `config/`, com destaque para `queue.php` e
  `filesystems.php` que definem filas e armazenamento.
- `bootstrap/` e `public/index.php` inicializam o framework.

### Código legado
- O diretório `legacy/` guarda controladores, views e scripts antigos
  ainda consumidos via rotas específicas ou jobs. Documentação auxiliar
  está em `docs/estrutura-legacy.md` e `docs/legacy-dependencias.md`.
- Assets legados continuam em `public/assets`, consumidos por páginas
  antigas que ainda não migraram para Blade/Vue.

### Frontend
- `resources/views` contém templates Blade modernos.
- `resources/css` guarda estilos processados pelo Vite (`vite.config.js`).
- `public/` serve tanto os assets construídos quanto entradas legadas.

### Testes e suporte a qualidade
- Testes automatizados ficam em `tests/` com as suites `Feature` e
  `Unit`.
- Ferramentas de análise vivem nos scripts Composer: `composer test`
  chama o PHPUnit, `composer lint` roda Pint + PHP_CodeSniffer e
  `composer analyse` executa o PHPStan.

## Fluxos críticos

### Correção de provas digitalizadas
1. `ExamCorrectionController@upload` recebe o cartão e salva metadados.
2. `ProcessExamScan::dispatch` processa o arquivo, chama
   `ExamAutoGrader` e atualiza registros via `ExamScan`/`ExamScanItem`.
3. Respostas consolidadas são gravadas no legado com PDO utilizando os
   helpers de suporte, garantindo compatibilidade com os relatórios
   antigos.

### Integração Moodle
1. Alterações em provas disparam `SyncExamWithMoodle`.
2. Notas publicadas pelo `ProcessExamScan` geram eventos consumidos por
   `SyncGradeWithMoodle`, que envia os resultados via `MoodleClient`.

### Planejamento e reservas
- `PlanningApiController` delega a `RoomReservationService` a criação e
  atualização das reservas (`RoomReservation`), enquanto valida perfis
  usando `App\Auth\Policies`.

## Pontos de extensão
- Novos endpoints: seguir o padrão dos controladores em
  `app/Http/Controllers`, reutilizando `Request` e `JsonResponse`.
- Novos jobs: declarar em `app/Jobs`, reutilizar `App\Support\EventDispatcher`
  para publicar eventos e registrar a fila em `config/queue.php`.
- Integrações externas: criar serviços em `app/Services` e expor
  dependências via construtor para facilitar mocking em testes.

Use este mapa em conjunto com os demais documentos de `docs/` para
apoiar decisões de arquitetura e facilitar o onboarding.
