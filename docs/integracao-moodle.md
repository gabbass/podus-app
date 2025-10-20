# Integração com Moodle

Este documento descreve os requisitos funcionais identificados nas telas atuais do portal e os respectivos mapeamentos para os endpoints REST do Moodle. O objetivo é alinhar o modelo de sincronização entre os sistemas, detalhando os recursos mínimos necessários para usuários, cursos/turmas e avaliações/notas.

## Visão geral da sincronização

| Domínio | Eventos no Portal | Objetivo no Moodle | Endpoint Moodle sugerido |
| --- | --- | --- | --- |
| Usuários | Cadastro e atualização de perfis (Administrador, Professor, Aluno) | Criar/atualizar contas mantendo perfil e matrícula | `core_user_create_users`, `core_user_update_users` |
| Cursos/Turmas | Criação/edição de turmas e vínculo com professores | Criar curso e matricular participantes | `core_course_create_courses`, `enrol_manual_enrol_users` |
| Avaliações (Provas) | Cadastro de prova (título, descrição, data/curso) | Criar atividade correspondente (quiz/assignment) no curso Moodle | `mod_quiz_create_quizzes` ou `mod_assign_create_assignments` |
| Notas | Lançamento/atualização de notas de prova | Atualizar item de nota para aluno | `core_grades_update_grades` |

> **Observação:** A escolha entre `mod_quiz_create_quizzes` e `mod_assign_create_assignments` depende do tipo de atividade que melhor representa a prova no Moodle. O cliente deve validar esta opção com a área pedagógica.

## Estruturas de payload

### Usuários
- **Campos mínimos do portal:** nome, e-mail, login, perfil (Administrador/Professor/Aluno), matrícula (para alunos), escola.
- **Mapeamento Moodle:**
  - `username`: login do portal (normalizado para slug e único).
  - `firstname`/`lastname`: derivados do nome completo (separar primeiro nome e sobrenome quando possível).
  - `email`: e-mail informado na tela.
  - `auth`: `manual`.
  - `idnumber`: matrícula ou identificador legado.
  - `customfields`: armazenar perfil/cliente quando necessário.

### Cursos/Turmas
- **Campos mínimos:** nome da turma, código do cliente, professor responsável, turno/etapa.
- **Mapeamento Moodle:**
  - `fullname`/`shortname`: nome e código da turma.
  - `categoryid`: categoria padrão definida para a escola.
  - `idnumber`: código legado da turma.
  - Vincular professor como `editingteacher` usando `enrol_manual_enrol_users`.
  - Matricular alunos com papel `student`.

### Provas
- **Campos mínimos:** título, descrição (opcional), curso/turma, data de aplicação e disponibilidade, tempo de duração, peso.
- **Mapeamento Moodle (quiz):**
  - `name`: título da prova.
  - `courseid`: ID Moodle da turma.
  - `intro`: descrição/observações.
  - `timeopen`/`timeclose`: janelas de aplicação.
  - `timelimit`: duração em segundos.
  - `grade`: nota máxima.

### Notas
- **Campos mínimos:** prova associada, aluno, nota obtida, data de lançamento, avaliador.
- **Mapeamento Moodle:**
  - `itemid` ou `itemnumber`: identificador da prova no Moodle.
  - `grades` → `userid`: aluno.
  - `grades` → `grade`: nota numérica convertida para escala do Moodle.
  - `grades` → `str_feedback`: observações do avaliador (opcional).

## Fluxo recomendado

1. **Sincronização prévia:** Antes de enviar prova ou nota, garantir que usuário e curso já estejam sincronizados e possuam seus respectivos IDs no Moodle.
2. **Cadastro de prova:** Ao salvar a prova no portal, enviar job assíncrono para criar a atividade correspondente (`mod_quiz_create_quizzes` ou `mod_assign_create_assignments`). Persistir a relação `portal_exam_id` ↔ `moodle_module_id` para uso posterior.
3. **Lançamento de nota:** Ao confirmar nota, enviar job que chame `core_grades_update_grades`, referenciando o `itemid` retornado no passo anterior e o `userid` Moodle do aluno.
4. **Tratamento de erros:** Guardar histórico com payload enviado, resposta recebida e status (sucesso, erro temporário, erro definitivo) para auditoria.

## Considerações técnicas

- Utilizar token de serviço dedicado configurado em `.env` (`MOODLE_TOKEN`).
- Endpoint base (`MOODLE_ENDPOINT`) deve apontar para a raiz do serviço REST (`https://exemplo.com/webservice/rest/server.php`).
- Timeout configurável (`MOODLE_TIMEOUT`) com fallback para 10 segundos.
- Utilizar JSON como formato padrão (`moodlewsrestformat=json`).
- Em caso de falha, registrar logs no banco e no log de aplicação para facilitar reprocessamento.

## Próximos passos

- Confirmar quais perfis do portal correspondem aos papéis Moodle (Administrador, Professor, Estudante).
- Definir estratégia de versionamento para IDs externos (guardar em tabelas auxiliares ou colunas dedicadas).
- Mapear demais eventos que precisem de sincronização (ex.: cancelamento de prova, exclusão de usuário, alteração de turma).

