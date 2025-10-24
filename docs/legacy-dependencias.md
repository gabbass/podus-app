# Dependências do frontend moderno em assets/serviços legados

## Metodologia

- `rg "legacy/" resources app public`
- `rg "includes/" resources app public`

Os comandos acima identificaram arquivos Blade e scripts do frontend Laravel que ainda fazem referência direta a assets e endpoints PHP localizados em `legacy/`.

## Mapeamento de dependências

| Dependência legada | Tipo | Consumido por (Laravel) | Como é utilizada | Classificação | Riscos de mudança | Pré-requisitos para refatoração |
| --- | --- | --- | --- | --- | --- | --- |
| `legacy/public/pustyle.css` | CSS global | `resources/views/layouts/app.blade.php` | Folha de estilos carregada em todas as páginas que utilizam o layout principal para preservar aparência e utilitários da UI antiga. | Migrar | Remover ou alterar o arquivo sem substituição quebra layout padrão (classes como `.legacy-app`, botões, grids). | Inventariar componentes que dependem das classes do CSS antigo; portar estilos críticos para a stack atual (ex.: SCSS/Tailwind via Vite); validar visualmente telas chaves após migração. |
| `legacy/js/provas.js` | JavaScript (CRUD) | `resources/views/exams/index.blade.php` | Controla toda a experiência de provas (modal CRUD, filtros, Choices.js) chamando endpoints AJAX do legado. | Migrar | Reescrita sem cuidado interrompe cadastro/edição de provas e integração com Choices.js, além de depender do markup Blade atual. | Criar controlador/API Laravel equivalente às ações (`listar*`, `buscar`, `criar/editar`, `excluir`); portar lógica para módulos JS modernos (ESM/Vite) preservando IDs do DOM ou reescrevendo a view. |
| `legacy/includes/action-provas.php` | Endpoint PHP | Consumido via fetch por `legacy/js/provas.js` em `resources/views/exams/index.blade.php` | API que lista/gerencia provas, turmas e matérias usando sessão do legado e acesso direto às tabelas `provas_online`, `turmas`, `questoes`, `bncc_componentes`. | Migrar | Manter endpoint fora do Laravel dificulta auditoria, logging e segurança; indisponibilidade rompe toda a tela de provas. | Replicar regras de permissão/sessão no Laravel, migrar queries para Eloquent ou Query Builder, configurar conexão BNCC, criar rotas protegidas substituindo o endpoint legado. |

## Itens não encontrados

Nenhuma referência ativa a `includes/` foi localizada em `resources/`, `app/` ou `public/`, além das chamadas indiretas via `legacy/js/provas.js` descritas acima.
