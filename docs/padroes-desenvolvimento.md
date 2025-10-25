# Padrões de Desenvolvimento

Este guia consolida as convenções utilizadas no PODUS App para manter o
código consistente entre as camadas Laravel e legado.

## Convenções gerais
- **Idiomas:** código e comentários em inglês; mensagens para usuário em
  português quando exibidas na interface.
- **Tipagem:** utilizar tipos estritos em propriedades, parâmetros e
  retornos (vide `App\Jobs\ProcessExamScan` e `App\Services\ExamAutoGrader`).
- **Validação antecipada:** retornar respostas de erro o quanto antes,
  seguindo os exemplos dos controladores em `app/Http/Controllers`.

## PHP (Laravel e legado)
- Controladores devem estender `App\Http\Controllers\Controller` e
  retornar `App\Http\JsonResponse` quando expostos via API.
- Centralize regras de negócio em serviços (`app/Services`) para evitar
  controladores com muita lógica.
- Acesso ao legado deve usar os helpers de `App\Support\LegacyDatabase`
  e `App\Support\LegacySchema` para garantir tratamento consistente de
  conexões PDO.
- Jobs devem ser idempotentes e registrar status pelos métodos auxiliares
  dos modelos (`ExamScan::markProcessing`, `RoomReservation::releaseFor` etc.).
- Preferir coleções e arrays imutáveis durante o processamento para
  facilitar testes.

## Sessão e autenticação
- Utilize `App\Support\Session\SessionManager` para manipular sessões,
  garantindo compatibilidade com o legado.
- Middlewares em `app/Auth/Middleware` encapsulam regras de perfil;
  reaproveite-os antes de criar novos.

## Frontend
- Templates Blade vivem em `resources/views` e devem estender layouts
  compartilhados sempre que possível.
- Estilos novos devem usar PostCSS/Vite (`resources/css`) e evitar
  adicionar assets manuais em `public/`.
- Componentes Vue (quando necessários) devem ser registrados via Vite e
  usar `npm run dev` para desenvolvimento.

## Testes e qualidade
- Novas funcionalidades precisam de testes em `tests/Feature` ou
  `tests/Unit`.
- Execute localmente:
  - `composer test` para PHPUnit.
  - `composer lint` para Pint + PHP_CodeSniffer.
  - `composer analyse` para PHPStan.
- Prefira fakes/mocks para serviços externos (`MoodleClient`) nos testes
  para evitar dependências de rede.

## Commits e PRs
- Commits devem ser atômicos, com mensagens no formato `<escopo>: <ação>`
  (ex.: `exam: prevent duplicate attempts`).
- Pull Requests precisam atualizar a documentação relevante em `docs/`
  ou no `README.md` quando alterarem fluxos de trabalho.

Seguir estes padrões ajuda a manter o repositório coeso e reduz o custo
de manutenção contínua.
