# API de agendamentos de salas

## Visão geral
O fluxo de reservas de salas é atendido exclusivamente pelo controlador moderno (`App\Http\Controllers\Api\PlanningApiController`), acessado via chamadas JSON para `/api/planning` com o parâmetro `acao`. As rotas expõem as operações do planejador e respeitam a política de permissões centralizada em `PermissionMatrix`. O endpoint PHP legado (`legacy/includes/action-planejamento-mensal.php`) foi desativado e agora responde `410 Gone` orientando clientes a migrarem para a API atual.【F:app/Http/Controllers/Api/PlanningApiController.php†L1-L327】【F:legacy/includes/action-planejamento-mensal.php†L1-L8】

O controlador usa `RoomReservationService`, que garante conflitos de agenda, vinculação ao planejamento, regras de aprovação e associação com escolas/professores.【F:app/Services/RoomReservationService.php†L1-L307】 As tabelas `rooms` e `room_reservations` são criadas via migrations dedicadas e relacionam reservas com escolas, usuários e planejamentos legados.【F:database/migrations/2024_07_18_000004_create_rooms_table.php†L1-L35】【F:database/migrations/2024_07_18_000005_create_room_reservations_table.php†L1-L78】

## Ações disponíveis
As ações abaixo aceitam e retornam JSON (`sucesso`, `mensagem`, `dados`). Quando necessário, envie os parâmetros no corpo (`POST`) ou na query string (`GET`).

| Ação (`acao`) | Método | Descrição | Permissão mínima |
| --- | --- | --- | --- |
| `listar_salas` | GET | Lista salas disponíveis (da escola do usuário ou compartilhadas). | `reservas.view` (todos os perfis autenticados) |
| `listar_reservas` | GET | Retorna reservas filtradas por sala, planejamento ou período. Inclui flags `pode_aprovar` e `pode_cancelar`. | `reservas.view` |
| `reservar_sala` | POST | Solicita/efetiva reserva de sala vinculada a um planejamento. Administradores aprovam automaticamente. | `reservas.create` |
| `aprovar_reserva` | POST | Aprova, rejeita ou cancela reservas pendentes. | `reservas.approve` (Administrador/Escola) |
| `cancelar_reserva` | POST | Cancela uma reserva pelo solicitante (ou por administradores via `aprovar_reserva` com decisão `cancelar`). | `reservas.create` (autoria) |

### Parâmetros comuns
- `room_id` (int) – identificador da sala.
- `planning_id` (int) – planejamento ao qual a reserva pertence (validação de titularidade para professores).
- `inicio` / `fim` (string, `YYYY-MM-DDTHH:MM`) – período da reserva, normalizado para `YYYY-MM-DD HH:MM:SS` pelo backend.【F:app/Services/RoomReservationService.php†L216-L243】【F:app/Http/Controllers/Api/PlanningApiController.php†L233-L268】
- `observacoes` (string opcional) – detalhes adicionais para o aprovador.
- `decisao` (`aprovar`, `rejeitar`, `cancelar`) – usada em `aprovar_reserva` para definir o novo status.【F:app/Http/Controllers/Api/PlanningApiController.php†L152-L205】

### Exemplos de chamadas

#### Listar reservas do planejamento 42
```http
GET /api/planning?acao=listar_reservas&planning_id=42
Authorization: Bearer <token>
Accept: application/json
```
Resposta (200):
```json
{
  "sucesso": true,
  "pode_aprovar": false,
  "reservas": [
    {
      "id": 10,
      "room_id": 3,
      "sala": "Laboratório 1",
      "inicio": "2024-08-01T12:00:00+00:00",
      "fim": "2024-08-01T13:00:00+00:00",
      "status": "pending",
      "solicitante": "Paulo Professor",
      "pode_cancelar": true
    }
  ]
}
```

#### Criar reserva aguardando aprovação
```http
POST /api/planning
Authorization: Bearer <token>
Content-Type: application/json

{
  "acao": "reservar_sala",
  "room_id": 3,
  "planning_id": 42,
  "inicio": "2024-08-01T12:00",
  "fim": "2024-08-01T13:00",
  "observacoes": "Aula prática"
}
```
Resposta (200): mensagem de sucesso com o objeto `reserva`. Professores recebem `auto_aprovada: false`; administradores recebem `true` e status `approved`. Conflitos são bloqueados com erro 400/422 conforme o motivo.【F:app/Services/RoomReservationService.php†L128-L193】

#### Aprovar reserva
```http
POST /api/planning
Authorization: Bearer <token-admin>
Content-Type: application/json

{
  "acao": "aprovar_reserva",
  "reserva_id": 10,
  "decisao": "aprovar"
}
```
Resposta (200): reserva atualizada com `status` `approved` e carimbo `aprovado_em`.

## Frontend moderno
O frontend Laravel consome a API via módulo ESM (`public/assets/js/modules/planning.js`) carregado pela view Blade `resources/views/planning/index.blade.php`. A página injeta permissões e identificadores do usuário via atributos `data-*`, popula o formulário de planejamento, agenda salas e coordena aprovações/cancelamentos conforme os flags retornados pela API.【F:public/assets/js/modules/planning.js†L1-L654】【F:resources/views/planning/index.blade.php†L1-L259】

## Observações adicionais
- Conflitos de horário consideram reservas `pending` e `approved` para a mesma sala; uma exceção `Já existe uma reserva para este período` é devolvida ao cliente.【F:app/Services/RoomReservationService.php†L244-L272】
- A propriedade `allowOverride` permite que administradores reservem salas para planejamentos de outros professores sem violar a regra de titularidade.【F:app/Services/RoomReservationService.php†L112-L158】
- O serviço garante limpeza automática ao excluir planejamentos (gatilho SQLite e FK no MySQL).【F:database/migrations/2024_07_18_000005_create_room_reservations_table.php†L45-L75】
