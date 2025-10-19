# Estrutura do legado PHP

A aplicação original foi movida para o diretório `legacy/` para facilitar a
migração gradual para Laravel. A nova organização separa páginas públicas,
parciais compartilhadas e scripts auxiliares.

```
legacy/
├── public/          # Páginas PHP acessíveis via web, assets estáticos e uploads
│   └── js -> ../js  # Link simbólico temporário para expor os scripts originais
├── includes/        # Parciais PHP reutilizadas e helpers de backend
└── js/              # Scripts JS, rotas AJAX e utilitários do legado
```

## Estratégia de migração para Laravel

| Bloco atual                | Conteúdo principal                                            | Destino planejado no Laravel                               |
|----------------------------|---------------------------------------------------------------|-------------------------------------------------------------|
| `legacy/public`            | Telas e endpoints front controller em PHP puro               | `resources/views/legacy` (Blade) + `app/Http/Controllers/Legacy` |
| `legacy/includes`          | Parciais como `menu.php`, `cabecalho.php`, helpers e uploads | `resources/views/legacy/partials` + serviços em `app/Legacy` |
| `legacy/js`                | Scripts JS e handlers AJAX                                   | `resources/js/legacy` (Vite) + rotas API em `routes/api.php` |

### Observações

- Os includes PHP foram atualizados para apontar para `legacy/includes`,
  garantindo que todas as páginas utilizem uma única fonte de `menu.php`,
  `cabecalho.php` e `rodape.php`.
- Os links simbólicos `legacy/public/js` e `legacy/public/includes` mantêm os
  caminhos públicos existentes enquanto os assets e endpoints são preparados
  para o pipeline do Laravel (Vite/mix ou rotas dedicadas).
- Durante a migração, recomenda-se mapear cada página de `legacy/public` para um
  controller dedicado em `app/Http/Controllers/Legacy`, encapsulando a lógica em
  serviços reutilizáveis antes de converter as views para Blade.
