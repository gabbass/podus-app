# Inventário do workflow de CI

## Objetivos das etapas em `.github/workflows/ci.yml`
- `composer install`: instala as dependências PHP declaradas no `composer.json` para que as demais etapas de análise e testes possam ser executadas com o autoload e ferramentas corretas.
- `pint` (`vendor/bin/pint --test`): executa o Laravel Pint em modo de verificação para garantir que o código PHP siga o padrão de formatação definido pelo projeto.
- `phpcs` (`vendor/bin/phpcs`): roda o PHP_CodeSniffer para identificar violações das regras de estilo e boas práticas configuradas no projeto.
- `phpstan` (`vendor/bin/phpstan analyse`): realiza a análise estática do código PHP usando PHPStan para detectar erros potenciais e problemas de tipagem.
- `phpunit` (`vendor/bin/phpunit --coverage-clover ... --min-coverage=80`): executa a suíte de testes automatizados com PHPUnit, gerando relatório de cobertura e exigindo cobertura mínima de 80%.

## Outros workflows no GitHub Actions
Não foi possível verificar se existem workflows ocultos ou desabilitados porque o ambiente atual não possui acesso à interface do GitHub Actions.

## Checks de branch protection
Não foi possível confirmar quais branches ou pull requests exigem o check "CI / build" nas regras de branch protection, pois a interface de configurações do repositório não está acessível neste ambiente.
