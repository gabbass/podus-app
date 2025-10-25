# PODUS App

Aplicação Laravel responsável pela gestão e correção de provas digitalizadas do PODUS. Este guia apresenta uma visão completa da arquitetura, descreve como configurar o ambiente local, executar fluxos importantes, rodar testes e preparar o deploy.

## Sumário

1. [Visão geral](#visão-geral)
2. [Arquitetura](#arquitetura)
3. [Requisitos](#requisitos)
4. [Configuração do ambiente](#configuração-do-ambiente)
5. [Execução da aplicação](#execução-da-aplicação)
6. [Fluxo de upload e correção](#fluxo-de-upload-e-correção)
7. [Jobs e filas](#jobs-e-filas)
8. [Testes](#testes)
9. [Build dos assets](#build-dos-assets)
10. [Deploy](#deploy)
11. [Troubleshooting](#troubleshooting)

## Visão geral

O PODUS App permite que escolas enviem imagens digitalizadas dos cartões-resposta dos alunos, faça o processamento dessas imagens, gere correções automáticas e disponibilize relatórios. A aplicação foi construída com:

- **Backend:** Laravel 10 (PHP 8.2)
- **Banco de dados:** MySQL/MariaDB
- **Filas:** Redis + Laravel Horizon
- **Frontend:** Blade + componentes Vue e uma camada legada servida diretamente via `public/assets`
- **Jobs assíncronos:** filas para validação, correção e geração de relatórios

## Arquitetura

```
app/
  Console/          # Comandos Artisan personalizados
  Http/
    Controllers/    # Controladores REST, APIs e controllers legados
    Middleware/     # Autenticação e autorização
  Jobs/             # Processos assíncronos (upload, correção, relatórios)
  Models/           # Modelos Eloquent e integrações com o legado
bootstrap/          # Autoloader e inicialização do framework
config/             # Configurações Laravel (fila, cache, mail, etc.)
database/
  migrations/       # Migrações de esquema
  seeders/          # Seeds para dados iniciais
legacy/             # Código legado (controllers, views e assets antigos)
public/             # Root HTTP, inclui `assets/` legados
resources/
  js/               # Vue components, utilitários
  views/            # Templates Blade modernos
routes/             # Arquivos de rota (web, api, console)
tests/              # Testes automatizados (Feature e Unit)
```

### Integração com o legado

O diretório `legacy/` contém scripts antigos que ainda são utilizados para geração de relatórios ou compatibilidade com sistemas existentes. O novo código Laravel chama essas rotinas por meio de serviços específicos localizados em `app/Services/Legacy`. Sempre que possível, preferir implementar novas funcionalidades no código moderno e encapsular chamadas ao legado.

## Requisitos

- PHP 8.2 com extensões: `curl`, `mbstring`, `openssl`, `pdo_mysql`, `redis`, `gd`
- Composer 2.5+
- Node.js 18+ e npm 9+
- MySQL 8 ou MariaDB 10.6
- Redis 6+ (para filas e cache)
- Opcional: Docker/Docker Compose para ambientes padronizados

## Configuração do ambiente

1. **Clonar o repositório**

   ```bash
   git clone git@github.com:podus/podus-app.git
   cd podus-app
   ```

2. **Instalar dependências PHP**

   ```bash
   composer install
   ```

3. **Instalar dependências JavaScript**

   ```bash
   npm install
   ```

4. **Configurar variáveis de ambiente**

   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

   - Defina as variáveis de conexão (`DB_*`) para o seu banco local.
   - Configure `REDIS_HOST`, `REDIS_PASSWORD` e `QUEUE_CONNECTION=redis` para processar filas.
   - Ajuste credenciais de serviços externos (ex.: armazenamento S3, providers de OCR) conforme necessário.

5. **Criar banco de dados**

   ```bash
   php artisan migrate --seed
   ```

6. **Configurar storage**

   ```bash
   php artisan storage:link
   ```

7. **Dados de desenvolvimento** (opcional)

   Execute `php artisan db:seed --class=DevelopmentSeeder` para popular dados fictícios de alunos, turmas e provas.

## Execução da aplicação

### Servidor HTTP

- **Servidor Laravel:**

  ```bash
  php artisan serve
  ```

  O serviço ficará disponível em `http://localhost:8000`.

- **Servidor com Valet ou Apache/Nginx:** configure o virtual host apontando para `public/`.

### Frontend

Para desenvolvimento com hot reload:

```bash
npm run dev
```

Para gerar assets de produção:

```bash
npm run build
```

## Fluxo de upload e correção

1. **Upload de cartões**
   - Endpoint: `POST /api/uploads` (autenticado via token ou sessão).
   - Recebe imagem do cartão e metadados (prova, turma, aluno).
   - Salva o arquivo em `storage/app/uploads/{prova}/{tentativa}/` com nome único.
   - Enfileira `ProcessUploadedCard` para validação.

2. **Validação**
   - Verifica integridade, checa duplicidade de tentativa e extrai dados OCR.
   - Atualiza status da tentativa (`pending`, `processing`, `completed`, `failed`).

3. **Correção**
   - Job `GradeAttemptJob` compara respostas com gabarito e escreve resultados em `provas_respostas`.
   - Gera arquivos auxiliares (`resposta_tentaX.pdf`, `gabarito_tentaX.pdf`).

4. **Relatórios**
   - Após correção, `GenerateReportsJob` atualiza dashboards e envia notificações.

5. **Reprocessamento**
   - `php artisan podus:retry-upload {upload_id}` reenvia um cartão para a fila em caso de falha.

## Jobs e filas

- Todas as filas utilizam conexão `redis` e a `queue:work` deve estar ativa.
- Utilize `php artisan horizon` para monitoramento em tempo real.
- Principais jobs:
  - `ProcessUploadedCard`
  - `GradeAttemptJob`
  - `GenerateReportsJob`
  - `DispatchLegacyReport`
- Para executar workers localmente:

  ```bash
  php artisan queue:work --queue=uploads,grading,reports
  ```

## Testes

- **PHPUnit**

  ```bash
  php artisan test
  ```

- **Testes de integração com filas** (requer Redis):

  ```bash
  php artisan test --group=queue
  ```

- **Análise estática**

  ```bash
  ./vendor/bin/phpstan analyse
  ./vendor/bin/phpcs
  ```

- **Frontend**

  ```bash
  npm run test
  ```

## Build dos assets

Os estilos e scripts utilizados pela interface legada são servidos diretamente a partir de `public/assets`. Para reconstruí-los em um ambiente limpo ou durante o deploy:

1. Copie os estilos necessários a partir da pasta legada:

   ```bash
   mkdir -p public/assets/css
   cp legacy/public/css/pustyle.css public/assets/css/
   cp legacy/public/css/legacy-tokens.css public/assets/css/
   ```

2. Garanta que os módulos JavaScript estejam presentes em `public/assets/js`:

   ```bash
   mkdir -p public/assets/js/modules
   cp legacy/public/js/app.js public/assets/js/
   cp legacy/public/js/modules/*.js public/assets/js/modules/
   ```

   > Os scripts já são versionados como módulos ES6 e podem ser servidos diretamente. Basta copiá-los para o destino de deploy.

3. Para os assets modernos, utilize o Vite:

   ```bash
   npm run build
   ```

## Deploy

1. `composer install --no-dev --optimize-autoloader`
2. `php artisan config:cache && php artisan route:cache`
3. Rodar `npm run build`
4. Copiar diretório `public/` para o destino (assegurando `public/assets` legados)
5. Executar migrações: `php artisan migrate --force`
6. Garantir que workers de fila estejam ativos (systemd ou supervisord)
7. Configurar backups automáticos para banco e storage

## Troubleshooting

- **Uploads duplicados**: verifique logs em `storage/logs/laravel.log` e entradas duplicadas em `provas_uploads`.
- **Jobs travados**: rode `php artisan horizon:purge` ou reinicie o worker.
- **Problemas com OCR**: confira configurações em `config/services.php` e credenciais no `.env`.
- **Assets ausentes**: execute as etapas da [Build dos assets](#build-dos-assets) e limpe caches do navegador.

## Documentação complementar

- [Mapa do código](docs/mapa-codigo.md): visão das camadas, fluxos críticos e pontos de extensão.
- [Padrões de desenvolvimento](docs/padroes-desenvolvimento.md): convenções para backend, legado e frontend.
- Consulte também `docs/` para guias específicos de integrações, infraestrutura e processos legados.

## Contribuindo

1. Crie uma branch a partir de `main`.
2. Siga o padrão PSR-12 e execute linters antes do commit.
3. Abra PR descrevendo mudanças, passos para testar e impacto esperado.

## Licença

Proprietário. Uso restrito à equipe PODUS.
