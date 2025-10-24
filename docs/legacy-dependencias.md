# Dependências do frontend moderno em assets/serviços legados

## Metodologia

- `rg "legacy/" resources app public`
- `rg "includes/" resources app public`

Os comandos acima identificaram arquivos Blade e scripts do frontend Laravel que ainda fazem referência direta a assets e endpoints PHP localizados em `legacy/`.

## Mapeamento de dependências

| Dependência legada | Tipo | Consumido por (Laravel) | Como é utilizada | Classificação | Riscos de mudança | Pré-requisitos para refatoração | Módulo atual |
| --- | --- | --- | --- | --- | --- | --- | --- |
| `legacy/public/pustyle.css` | CSS global | `resources/views/layouts/app.blade.php` | Folha de estilos carregada em todas as páginas que utilizam o layout principal para preservar aparência e utilitários da UI antiga. | Migrar | Remover ou alterar o arquivo sem substituição quebra layout padrão (classes como `.legacy-app`, botões, grids). | Inventariar componentes que dependem das classes do CSS antigo; portar estilos críticos para a stack atual (ex.: SCSS/Tailwind via Vite); validar visualmente telas chaves após migração. | Layout global |
| `legacy/js/provas.js` | JavaScript (CRUD) | `resources/views/exams/index.blade.php` | Controlava toda a experiência de provas (modal CRUD, filtros, Choices.js) chamando endpoints AJAX do legado. Substituído por `public/assets/js/modules/exams.js` e arquivado em `legacy/archive/js/provas.js`. | Migrado | Reescrita sem cuidado interrompe cadastro/edição de provas e integração com Choices.js, além de depender do markup Blade atual. | Controlador/API Laravel em `public/api/exams.php` com módulo ESM moderno consumindo as novas rotas. | Provas |
| `legacy/includes/action-provas.php` | Endpoint PHP | Consumido via fetch por `legacy/js/provas.js` em `resources/views/exams/index.blade.php` | API que listava/gerenciava provas, turmas e matérias usando sessão do legado e acesso direto às tabelas `provas_online`, `turmas`, `questoes`, `bncc_componentes`. Substituída por `App\Http\Controllers\Api\ExamController` e rota `public/api/exams.php`; arquivo legado movido para `legacy/archive/includes/action-provas.php`. | Migrado | Manter endpoint fora do Laravel dificulta auditoria, logging e segurança; indisponibilidade rompia toda a tela de provas. | Controlador Laravel com políticas atuais e rotas autenticadas. | Provas |

## Itens não encontrados

Nenhuma referência ativa a `includes/` foi localizada em `resources/`, `app/` ou `public/`, além das chamadas indiretas via `legacy/js/provas.js` descritas acima.
