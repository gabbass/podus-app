# podus-app

App do PODUS

## Assets legados

Os estilos e scripts utilizados pela interface legada são servidos diretamente a partir de `public/assets`. Para reconstruir esses assets em um ambiente limpo ou durante o deploy:

1. Copie os estilos necessários a partir da pasta legada:

   ```bash
   mkdir -p public/assets/css
   cp legacy/public/css/pustyle.css public/assets/css/
   cp legacy/public/css/legacy-tokens.css public/assets/css/
   ```

2. Garanta que os módulos JavaScript estejam presentes em `public/assets/js`:

   ```bash
   mkdir -p public/assets/js/modules
   cp public/assets/js/app.js $DESTINO/assets/js/
   cp public/assets/js/modules/*.js $DESTINO/assets/js/modules/
   ```

   > Os scripts já são versionados como módulos ES6 e podem ser servidos diretamente. Basta copiá-los para o destino de deploy.

Os templates Blade carregam `asset('assets/css/pustyle.css')` e `asset('assets/js/app.js')`, portanto esses caminhos precisam existir após o processo de deploy.
