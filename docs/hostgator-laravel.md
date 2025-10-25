# Guia de implantação no HostGator para o Podus (Laravel)

Este documento descreve um passo a passo para preparar o ambiente de hospedagem compartilhada da HostGator e publicar o projeto **Podus** em produção utilizando Laravel.

## Pré-requisitos

1. Plano HostGator com acesso ao **cPanel** e **SSH** (preferencial).
2. Domínio ou subdomínio configurado no HostGator.
3. PHP >= 8.1 com extensões obrigatórias do Laravel (`OpenSSL`, `PDO`, `Mbstring`, `Tokenizer`, `XML`, `Ctype`, `JSON`, `BCMath`, `Fileinfo`).
4. Banco de dados MySQL criado no cPanel (nome, usuário e senha).
5. Chave SSH adicionada ao painel (opcional, mas recomendado para `git pull`).

## Estrutura de diretórios

A hospedagem compartilhada expõe o diretório `public_html/` como raiz pública. Para o Laravel é recomendado manter os arquivos de aplicação **fora** desse diretório, expondo apenas o conteúdo da pasta `public/`.

```
/home/usuario/
├── podus-app/        # raiz do projeto Laravel
│   ├── app/
│   ├── bootstrap/
│   ├── config/
│   ├── public/
│   ├── ...
├── public_html/
│   └── index.php     # symlink para ../podus-app/public/index.php
```

## Passo a passo

1. **Upload do código**
   - Opção A: clone o repositório via SSH (`git clone`) na pasta `/home/usuario/podus-app`.
   - Opção B: compacte o projeto localmente (`zip`) e envie pelo Gerenciador de Arquivos do cPanel, extraindo em `/home/usuario/podus-app`.

2. **Configurar dependências PHP**
   - Acesse o terminal SSH e navegue até `/home/usuario/podus-app`.
   - Execute `composer install --no-dev --optimize-autoloader`. Se o HostGator não permitir Composer nativo, execute o comando localmente e envie a pasta `vendor/` já instalada.
   - Rode `php artisan key:generate` para definir a `APP_KEY`.

3. **Configurar variáveis de ambiente**
   - Copie `.env.example` para `.env`.
   - Ajuste as chaves:
     - `APP_URL=https://seusubdominio.com`
     - `APP_ENV=production`
     - `APP_DEBUG=false`
     - Credenciais `DB_*` conforme o banco criado no cPanel.
     - Configurações de e-mail (SMTP) disponibilizadas pelo HostGator.

4. **Permissões de diretórios**
   - Garanta que `storage/` e `bootstrap/cache/` sejam graváveis: `chmod -R 775 storage bootstrap/cache`.
   - Se necessário, defina o grupo do servidor web: `chgrp -R nobody storage bootstrap/cache`.

5. **Publicar a pasta `public/`**
   - Renomeie o arquivo atual `/home/usuario/public_html/index.php` para backup.
   - Crie um *symlink* ou atualize o documento raiz:
     ```bash
     ln -s /home/usuario/podus-app/public /home/usuario/public_html
     ```
     Se o HostGator bloquear `ln -s`, copie manualmente o conteúdo da pasta `public/` para `public_html/` e atualize as referências no `index.php` para apontar para `../vendor/autoload.php` e `../bootstrap/app.php`.

6. **Configurar *cron jobs***
   - No cPanel, adicione um cron para executar o scheduler do Laravel a cada minuto:
     ```
     * * * * * php /home/usuario/podus-app/artisan schedule:run >> /dev/null 2>&1
     ```

7. **Cache e otimizações (opcional)**
   - `php artisan config:cache`
   - `php artisan route:cache`
   - `php artisan view:cache`

8. **Deploy contínuo (opcional)**
   - Configure um *webhook* do Git ou use `git pull` manualmente sempre que houver atualização.
   - Após atualizar o código, execute `php artisan migrate --force` se houver novas migrações.

## Resolução de problemas

- **500 Internal Server Error**: verifique permissões dos diretórios graváveis e logs em `storage/logs/laravel.log`.
- **Composer indisponível**: instale localmente e faça upload da pasta `vendor/`.
- **Timeout em scripts**: ajuste `max_execution_time` e `memory_limit` no `MultiPHP INI Editor`.
- **Erros de permissões em cache**: limpe caches com `php artisan optimize:clear`.

## Verificação pós-deploy

1. Acesse a URL do domínio e valide o carregamento da aplicação.
2. Execute testes de fluxo críticos (autenticação, agendamentos, relatórios).
3. Monitore logs de erro nas primeiras horas após o deploy.

---
Para dúvidas adicionais, consulte a documentação do HostGator ou abra um chamado com a equipe de infraestrutura.
