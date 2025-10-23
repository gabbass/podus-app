# Correção automática de provas (OMR)

Este documento descreve a integração de leitura de cartões de resposta por
processamento ótico (Optical Mark Recognition) adotada pelo Podus. A solução foi
padronizada para operar em dois modelos:

- **SaaS OMR** (padrão): utiliza um provedor externo especializado. O endpoint
  e as credenciais são configurados via variáveis de ambiente
  `EXAM_OMR_SAAS_*`.
- **Microserviço próprio**: opção para instalações on-premise. O serviço deve
  expor a mesma API do SaaS e é configurado pelas variáveis
  `EXAM_OMR_SELF_HOSTED_*`.

A seleção do modo é feita através de `EXAM_OMR_DRIVER`, que aceita os valores
`saas` (default) ou `self_hosted`. O timeout de comunicação, limites de
confiança e número máximo de tentativas por aluno também são configuráveis.

## Variáveis de ambiente

| Variável | Descrição |
|----------|-----------|
| `EXAM_OMR_DRIVER` | Define o driver ativo (`saas` ou `self_hosted`). |
| `EXAM_OMR_SAAS_BASE_URL` | Base URL do provedor SaaS. |
| `EXAM_OMR_SAAS_API_KEY` | Token Bearer utilizado no SaaS. |
| `EXAM_OMR_SELF_HOSTED_BASE_URL` | Base URL do microserviço próprio. |
| `EXAM_OMR_SELF_HOSTED_TOKEN` | Token utilizado pelo microserviço próprio. |
| `EXAM_OMR_TIMEOUT` | Tempo máximo (segundos) para aguardar a resposta do provedor. |
| `EXAM_OMR_MIN_CONFIDENCE` | Confiança mínima aceitável para considerar uma marcação válida. |
| `EXAM_OMR_REVIEW_THRESHOLD` | Limite a partir do qual o sistema marca o scan como “revisar manualmente”. |
| `EXAM_OMR_MAX_ATTEMPTS` | Número máximo de tentativas por aluno (default: 3). |

## Contrato da API de leitura

O serviço `App\Services\ExamAutoGrader` envia uma requisição `POST` multipart
para o endpoint `/scans` do provedor com o arquivo (`file`), o identificador da
prova (`exam_id`) e a matrícula do aluno (`student_id`). O provedor deve
responder com o JSON abaixo:

```json
{
  "scan_id": "1c9bb6f4-3d0c-4564-9827-3a3e2c56f310",
  "status": "completed",
  "confidence": 0.93,
  "answers": [
    {
      "question_id": 1201,
      "choice": "B",
      "confidence": 0.97,
      "status": "detected",
      "metadata": {
        "coordinates": [120, 455, 160, 495]
      }
    },
    {
      "question_id": 1202,
      "choice": null,
      "confidence": 0.41,
      "status": "blank"
    }
  ],
  "metadata": {
    "pages": 1,
    "processing_time_ms": 1840
  }
}
```

O serviço normaliza esta resposta e traduz para o formato utilizado pelo legado:
- o campo `choice` é convertido para as colunas `resposta_tentaN` de
  `respostas_alunos`;
- o gabarito (`questoes.resposta`) é obtido via `LegacyConfig::createPdo()`;
- a nota é calculada como `(acertos / total) * 10` e persiste em `provas`.

Quando o `confidence` geral ou de alguma questão ficar abaixo de
`EXAM_OMR_REVIEW_THRESHOLD`, o scan é marcado com `requires_review = 1` na tabela
`exam_scans` para sinalizar revisão manual.

## Fluxo resumido

1. O controlador `App\Http\Controllers\ExamCorrectionController` recebe o
   upload e salva o arquivo em `storage/app/exams`.
2. É criado um registro em `exam_scans` com status `pending` e a tentativa
   calculada com base nas colunas `tentativa_feita`/`nota_tentaN`.
3. O job `App\Jobs\ProcessExamScan` aciona o `ExamAutoGrader`, cria entradas em
   `exam_scan_items`, atualiza `respostas_alunos`/`provas` e publica a nota via
   `GradeReleased`, disparando `SyncGradeWithMoodle` quando possível.
4. Logs de sincronização são persistidos em `moodle_sync_logs` através do job de
   notas.

## Revisão manual e fallback

Scans sinalizados com `requires_review = 1` permanecem disponíveis na consulta da
API (`GET /api/exam-scans?scan_id=...`). Para esses casos a rotina de job não
atualiza `tentativa_feita` até que um operador valide os dados no painel. O
operador pode:

1. Reprocessar o arquivo (novo upload) após ajustar o cartão físico; ou
2. Editar manualmente as respostas na tela de notas (`legacy/public/notas-alunos.php`).

Ao finalizar a revisão manual, recomenda-se atualizar a coluna correspondente em
`exam_scans` (`requires_review = 0`) para que o histórico reflita que o caso foi
resolvido.
