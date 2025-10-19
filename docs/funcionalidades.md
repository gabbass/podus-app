# Mapeamento de funcionalidades e dependências

## planejador-mensal.php
- **Objetivo**: Interface principal para professores e administradores gerenciarem planejamentos mensais de aula, permitindo criar, filtrar, listar, editar, exportar e imprimir planos alinhados à BNCC.【F:planejador-mensal.php†L1-L53】【F:js/download-plano.js†L1-L20】【F:js/imprimir-planejamento.js†L1-L16】
- **Perfis atendidos**: Requer sessão via `sessao-adm-professor.php`, liberando acesso a usuários com perfil de Professor ou Administrador armazenado em `$_SESSION['perfil']`/`$_SESSION['id']`.【F:planejador-mensal.php†L1-L4】【F:includes/action-planejamento-mensal.php†L44-L56】
- **Includes acionados**:
  - Layout e navegação: `includes/head.php`, `includes/menu.php`, `includes/cabecalho.php`, `includes/rodape.php`, `includes/foot.php`.
  - Fluxo de dados: `includes/crud-planejamento-mensal.php` (formulário e plugins Summernote/Choices), `includes/filtro-planejamento-mensal.php` (filtros de busca), `includes/lista-planejamento-mensal.php` (listagem com consultas SQL), `includes/modal-geral.php` (confirmações), `includes/action-planejamento-mensal.php` (CRUD via AJAX), `includes/action-bncc.php` (mapas BNCC), `includes/add-linha.php` (template de blocos), `includes/exp_docx_direto.php` (exportação DOCX).【F:planejador-mensal.php†L4-L53】【F:includes/crud-planejamento-mensal.php†L1-L75】【F:includes/filtro-planejamento-mensal.php†L1-L25】【F:includes/lista-planejamento-mensal.php†L1-L89】【F:js/planejamento-mensal.js†L35-L74】【F:js/download-plano.js†L1-L20】
- **Scripts JS**:
  - `js/crud-linha.js`: controla blocos de BNCC, serialização das linhas e integração com `action-planejamento-mensal.php` para etapas, anos, componentes e habilidades.【F:js/crud-linha.js†L1-L83】【F:js/crud-linha.js†L104-L154】
  - `js/openai.js` e `js/ai-sugestao.js`: suporte a sugestões automatizadas (disponibilizados junto ao CRUD).
  - `js/planejamento-mensal.js`: orquestra carregamento inicial, eventos do CRUD e fetch dos endpoints BNCC/planejamento.【F:js/planejamento-mensal.js†L1-L112】
  - `js/download-plano.js`: gera download do plano em DOCX.【F:js/download-plano.js†L1-L20】
  - `js/imprimir-planejamento.js`: abre versão de impressão do plano.【F:js/imprimir-planejamento.js†L1-L16】
- **Modais**: `includes/modal-geral.php` fornece modal Bootstrap reutilizado para confirmações de exclusão e avisos dentro do fluxo.【F:includes/modal-geral.php†L1-L15】【F:js/planejamento-mensal.js†L13-L37】
- **Dependências de dados**:
  - Sessão: `$_SESSION['id']` (filtra autor do planejamento), `$_SESSION['perfil']` (controle de privilégios), `$_SESSION['login']` (ao consultar matérias e BNCC em includes auxiliares).【F:includes/lista-planejamento-mensal.php†L9-L20】【F:includes/action-planejamento-mensal.php†L44-L60】
  - Tabelas principais (`conexao.php`): `planejamento`, `planejamento_linhas`, `materias` (nome da disciplina) e `login` (metadados do usuário); tabelas BNCC (`conexao-bncc.php`): `bncc_componentes`, `bncc_etapas`, `bncc_anos`, `bncc_areas`, `bncc_unidades_tematicas`, `bncc_objetos_conhecimento`, `bncc_habilidades`.【F:includes/lista-planejamento-mensal.php†L9-L61】【F:includes/action-planejamento-mensal.php†L66-L160】【F:includes/action-bncc.php†L1-L33】
  - Endpoints auxiliares: `includes/action-planejamento-mensal.php` (CRUD completo + filtros por perfil), `includes/action-bncc.php` (mapas para Choices/Summernote), `/portal/includes/exp_docx_direto.php` (geração de DOCX) e `planejador-mensal-visualizar.php` (renderização para impressão).【F:js/planejamento-mensal.js†L35-L112】【F:js/download-plano.js†L1-L20】【F:js/imprimir-planejamento.js†L9-L15】

## provas.php
- **Objetivo**: Backoffice para montagem de provas online, permitindo listar, filtrar, criar, editar, visualizar e excluir avaliações associadas a turmas e matérias.【F:provas.php†L1-L45】【F:js/provas.js†L1-L116】
- **Perfis atendidos**: Protegido por `sessao-adm-professor.php`; atende Professores (operações filtradas por `$_SESSION['login']`) e Administradores (tratados em `action-provas.php`).【F:provas.php†L1-L8】【F:includes/action-provas.php†L1-L73】
- **Includes acionados**:
  - Estrutura: `includes/head.php`, `includes/menu.php`, `includes/cabecalho.php`, `includes/rodape.php`, `includes/foot.php`.
  - Fluxo de provas: `includes/crud-provas.php` (formulário com carregamento inicial de matérias/questões), `includes/filtro-provas.php` (filtros de texto), `includes/lista-provas.php` (tabela paginada), `includes/modal-geral.php` (confirmações) e `includes/action-provas.php` (API AJAX com múltiplas ações).【F:provas.php†L4-L45】【F:includes/crud-provas.php†L1-L52】【F:includes/filtro-provas.php†L1-L16】【F:includes/lista-provas.php†L1-L9】【F:includes/modal-geral.php†L1-L15】【F:includes/action-provas.php†L1-L120】
- **Scripts JS**:
  - `js/provas.js`: controla estado do CRUD (Choices.js, carga de turmas/matérias/questões, abertura de visualização e exclusão com modal).【F:js/provas.js†L1-L124】【F:js/provas.js†L124-L189】
  - Dependências de terceiros importadas dentro do CRUD: jQuery e Choices.js são carregados por `includes/crud-provas.php` para alimentar os componentes dinâmicos.【F:includes/crud-provas.php†L8-L24】
- **Modais**: Usa `includes/modal-geral.php` para confirmações de exclusão, alertas e fluxos de visualização rápida.【F:js/provas.js†L7-L20】【F:includes/modal-geral.php†L1-L15】
- **Dependências de dados**:
  - Sessão: `$_SESSION['login']`, `$_SESSION['perfil']`, `$_SESSION['id']` controlam visibilidade e filtros de turmas/questões/provas; `sessao-adm-professor.php` garante autenticação mútua.【F:includes/action-provas.php†L8-L73】
  - Tabelas principais (`conexao.php`): `provas_online`, `turmas`, `questoes`, `login` (filtrar por escola/perfil). Tabelas BNCC (`conexao-bncc.php`): `bncc_componentes` para catálogo de matérias.【F:includes/action-provas.php†L24-L109】【F:includes/crud-provas.php†L1-L35】
  - Fluxos auxiliares: exportação/visualização via `provas-visualizar.php`, integração com `includes/action-provas.php` (`listar`, `buscar`, `criar`, `editar`, `excluir`) e seleção de questões com `includes/action-provas.php?acao=listarQuestoes` (respeita `isRestrito` e propriedade).【F:js/provas.js†L43-L115】【F:includes/action-provas.php†L49-L120】

## dashboard-professor.php
- **Objetivo**: Painel inicial para professores, exibindo contadores (questões, alunos, turmas) e um gráfico de distribuição de alunos por turma.【F:dashboard-professor.php†L1-L53】【F:dashboard_stats.php†L1-L31】
- **Perfis atendidos**: `sessao-professor.php` restringe o acesso a usuários autenticados no perfil Professor (compartilhado com Administrador em sessões específicas).【F:dashboard-professor.php†L1-L4】
- **Includes acionados**:
  - Estrutura: `includes/head.php`, `includes/menu.php`, `includes/cabecalho.php`, `includes/rodape.php`, `includes/foot.php`.
  - Dados e visualização: `dashboard_stats.php` (consulta banco e popula variáveis PHP), `grafico_pizza.js` (script inline com Chart.js para renderização), CDN do Chart.js.【F:dashboard-professor.php†L4-L53】【F:grafico_pizza.js†L1-L28】
- **Scripts JS**: carrega Chart.js via CDN e `grafico_pizza.js`, que injeta variáveis do PHP para desenhar o gráfico `alunosTurmaChart`. Não há scripts customizados adicionais além dos globais incluídos pelo rodapé.【F:dashboard-professor.php†L46-L53】【F:grafico_pizza.js†L1-L28】
- **Modais**: Esta tela não referencia modais diretamente; navegação ocorre por cliques nos cards.
- **Dependências de dados**:
  - Sessão: `$_SESSION['login']` é usada por `dashboard_stats.php` para filtrar alunos e turmas da escola vinculada; demais contadores usam consultas agregadas globais.【F:dashboard_stats.php†L1-L27】
  - Tabelas (`conexao.php`): `questoes`, `login` (com filtro por `perfil` e `turma`), `turmas`. O gráfico utiliza a mesma tabela `login` agrupada por turma e aplica geração de cores no PHP.【F:dashboard_stats.php†L1-L32】

## planejador.php
- **Objetivo**: Página legada de listagem geral de planejamentos (provavelmente para Administrador), permitindo pesquisar planos por curso ou ano e acessar ações (cadastro, edição, exclusão, impressão) diretamente via layout estático.【F:planejador.php†L1-L70】【F:planejador.php†L70-L140】
- **Perfis atendidos**: Não aplica middleware de sessão específico, mas depende de `conexao.php`; acesso deve ser restrito manualmente no servidor. Fluxo atual é orientado a usuários administrativos que gerenciam a tabela `planejador` inteira.【F:planejador.php†L1-L13】
- **Includes acionados**: Página standalone com HTML e CSS inline; não utiliza includes de layout, apenas `conexao.php` para query inicial e referências diretas a outros entrypoints (`planejador-cadastrar.php`, `planejador-editar.php`, `planejador-excluir.php`, `planejador-visualizar.php`).【F:planejador.php†L1-L13】【F:planejador.php†L140-L210】
- **Scripts JS e modais**: Não referencia arquivos JS dedicados ou modais compartilhados; interações ocorrem por botões/linkagens diretas em HTML.
- **Dependências de dados**:
  - Tabelas (`conexao.php`): consulta `planejador` para listar registros com filtros `curso` e `ano` e ordenação por `data`.【F:planejador.php†L5-L16】
  - Ausência de filtros por sessão indica que todos registros são retornados; eventual controle deverá ser refeito na modelagem Laravel.

## dashboard.php
- **Objetivo**: Dashboard administrativo clássico com contadores de professores, questões e artigos para usuários autenticados como administradores.【F:dashboard.php†L1-L21】
- **Perfis atendidos**: Protegido por `sessao-adm.php`, garantindo que apenas administradores autenticados executem as consultas agregadas.【F:dashboard.php†L1-L4】
- **Includes acionados**: Utiliza apenas `conexao.php` e renderiza HTML/CSS inline para cards e navegação lateral; não compartilha o layout modular das páginas mais novas.【F:dashboard.php†L1-L70】
- **Scripts JS e modais**: Não há scripts dedicados ou modais; os cards são estáticos.
- **Dependências de dados**:
  - Tabelas (`conexao.php`): `login` (filtrando `perfil = 'Professor'`), `questoes`, `artigo` (contagens agregadas). Todas as consultas rodam com privilégios administrativos sem filtragem por sessão específica além da autenticação prévia.【F:dashboard.php†L5-L20】

> **Observação**: Outras páginas da raiz seguem padrões semelhantes — novas páginas reutilizam os includes modulares e `modal-geral.php`, enquanto telas legadas mantêm HTML e CSS inline. Este documento prioriza os fluxos ativos citados no levantamento inicial para facilitar a migração gradual ao Laravel.
