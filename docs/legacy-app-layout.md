# Layout `legacy-app`

## Visão geral
O layout base `resources/views/layouts/app.blade.php` estrutura as telas legadas em três blocos principais:

- `<x-menu>`: barra lateral fixa que expõe a navegação principal.
- `<x-header>`: cabeçalho com botão hamburguer, área de alertas e menu do usuário.
- `<main>`: área de conteúdo que hospeda `@yield('content')` ou o `slot` fornecido.

O `<body>` recebe a classe `legacy-app` e, opcionalmente, os modificadores a seguir:

| Classe | Origem | Efeito |
| --- | --- | --- |
| `is-sidebar-collapsed` | estado inicial via `$sidebarState['collapsed']` ou interação do JS | Reduz a largura da barra lateral utilizando `--sidebar-width-collapsed` |
| `is-sidebar-overlay` | breakpoint ativo detectado pelo módulo `menu.js` ou `$sidebarState['overlay']` | Move a barra lateral para comportamento sobreposto (mobile/tablet) |
| `is-sidebar-open` | interação do usuário (JS) ou `$sidebarState['open']` | Exibe a barra lateral quando em modo sobreposto |

Quando existem breakpoints personalizados, o atributo `data-menu-breakpoints` carrega um JSON com a configuração para o módulo `menu.js`.

### Dependências estruturais
- **IDs e seletores esperados pelo JS**
  - `#sidebar`: elemento `<aside>` da navegação.
  - `#menu-toggle`: botão no cabeçalho para colapsar/exibir a barra lateral.
  - `#close-sidebar`: botão interno exibido apenas no modo sobreposto.
  - `#main-content`: `<main>` que abriga o cabeçalho e o conteúdo.
  - `[data-sidebar-backdrop]`: backdrop que cobre o conteúdo quando a barra lateral está aberta em overlay.
- **Componentes Blade**
  - `<x-menu>`: recebe `title`, `items`, `current`, `collapsed` e `overlay-open`.
  - `<x-header>`: recebe `alerts`, `avatar`, `name`, `profile`, `user-menu`, `is-sidebar-collapsed`, `is-sidebar-overlay` e `is-sidebar-open`.

## Views que utilizam o layout

| View | Tipo de uso |
| --- | --- |
| `resources/views/exams/index.blade.php` | Invoca `<x-layouts.app>` como componente, recebendo `$user` e usando o slot para o conteúdo da página. |
| `resources/views/professor/painel.blade.php` | Estende o layout via `@extends('layouts.app', [...])`, informando títulos, menu lateral e dados do usuário. |

Outras páginas Blade podem utilizar o layout desde que forneçam as mesmas variáveis opcionais (`$sidebarTitle`, `$menuItems`, `$alerts`, `$userMenu`, etc.).

## Sidebar e Header

- **Sidebar**
  - Classe principal: `.layout-sidebar` (também registra `.sidebar` para compatibilidade legada).
  - O menu recebe os itens normalizados (`label`, `url`, `icon`, `active`).
  - Estados manipulados: `collapsed` (layout compacto) e `overlay-open` (sidebar visível em telas pequenas).

- **Header**
  - Classe principal: `.layout-header`/`.top-nav`.
  - Botão hamburguer controla classes no `<body>` e atualiza `aria-expanded`.
  - O menu do usuário (`#user-menu`) utiliza o atributo `hidden` para controle de visibilidade.
  - Alertas são renderizados dentro de `#alertas-area` usando classes `.alert`.

## Integração com CSS e JS

- Os estilos da barra lateral e do cabeçalho residem em `legacy/public/css/pustyle.css`, que importa `legacy/public/css/legacy-tokens.css`.
- O arquivo `legacy/public/css/style.css` reaproveita os mesmos tokens para cores, tipografia e breakpoints.
- O módulo `resources/js/modules/menu.js` inicializa o comportamento responsivo utilizando os seletores citados e expõe funções globais utilizadas nas telas legadas (`initResponsiveMenu`, `toggleUserMenu`, `mostrarAlerta`, etc.).
