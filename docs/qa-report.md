# Relatório de QA - Provisionamento e Testes

## 1. Provisionamento do ambiente
- `composer install`: **não executado**. O projeto não possui `composer.json` ou `artisan`, impossibilitando o fluxo padrão do Laravel.
- `.env`: arquivo criado localmente a partir de `.env.example` (não versionado), ajustando banco para credenciais de desenvolvimento e filas `sync/redis`.
- `APP_KEY`: não gerado. A ausência do `artisan` impede o comando `php artisan key:generate`.

## 2. Migrações e seeders
- Não há diretório `database/migrations` com migrações utilizáveis nem suporte a `php artisan migrate`. Também não existem seeders compatíveis com Laravel.
- Base de dados para testes limitada a estruturas ad-hoc utilizadas em `tests/Authorization/PermissionMatrixTest.php` via SQLite em memória.

## 3. Testes existentes
- Suite disponível: `php tests/run.php` (passou com 2 arquivos carregados).
- Cobertura atual concentra-se nas classes de autorização (`LegacySessionGuard`, middlewares e `PermissionMatrix`).
- **Lacunas identificadas**:
  - Controllers ou rotas web não possuem testes.
  - Services em `app/Services` e integrações com Moodle seguem sem cobertura.
  - Não há testes específicos para policies, filas ou jobs.

## 4. Análise estática
- Ferramentas recomendadas (`vendor/bin/pint`, `vendor/bin/phpstan`) indisponíveis pelo mesmo motivo do Composer.
- Sugestão: adicionar `composer.json` com dependências de desenvolvimento para habilitar o pipeline.

## 5. Testes ponta a ponta
- Laravel Dusk/Cypress não configurados.
- Não existe estrutura de rotas Laravel; legado PHP (`legacy/`) opera com páginas estáticas/dinâmicas sem framework.
- Recomendação: documentar cenários prioritários (autenticação, planejamento mensal, agendamentos, dashboards) e definir estratégia de testes funcional por perfil (ADM, Escola, Professor, Aluno) após padronizar framework.

## 6. Integrações externas (Moodle)
- Serviços Moodle residem em `app/Services/Moodle` (sem mocks configurados).
- Não há ambientes de teste ou stubs automáticos. Criar fakes/mocks seria necessário para automatizar sincronização de usuários/notas.
- Logs dependem de infraestrutura externa; nenhuma simulação pôde ser executada.

## Próximos passos sugeridos
1. Versionar `composer.json` com dependências Laravel/Lumen (ou framework equivalente) para liberar Composer, Artisan, Pint, PHPStan e migrations.
2. Definir uma camada de testes (PHPUnit/Pest) com fixtures para controllers e serviços críticos.
3. Preparar seeds específicos para cenários Moodle e filas antes de investir em E2E.
