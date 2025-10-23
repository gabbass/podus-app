# Configuração do Universo do Saber (legado)

A camada `config/legacy.php` centraliza a leitura das variáveis de ambiente
utilizadas pelo código PHP existente. Copie o arquivo `.env.example` para `.env`
na raiz do projeto e ajuste os valores conforme necessário. Nenhum arquivo PHP
precisa ser editado para atualizar credenciais, rótulos de interface ou chaves
de integração.

## Variáveis obrigatórias

| Variável | Descrição | Valor padrão (`.env.example`) |
| --- | --- | --- |
| `DB_HOST`, `DB_PORT`, `DB_NAME`, `DB_USER`, `DB_PASS`, `DB_CHARSET` | Parâmetros de conexão com o MySQL. | `127.0.0.1`, `3306`, `podus_app`, `podus_app`, `secret`, `utf8mb4` |
| `QUEUE_CONNECTION`, `QUEUE_REDIS_*` | Configuração de filas do legado. | `sync`, `127.0.0.1`, `6379`, `null`, `0` |
| `APP_TITLE` | Título utilizado em páginas públicas. | `Podus - Portal Educacional` |
| `UI_MENUS_JSON` | Mapa JSON de menus por perfil (Professor, Administrador, Aluno). | Conteúdo idêntico ao menu atual |
| `MAIL_INVITE_*`, `MAIL_RECOVERY_*`, `MAIL_CONTACT_*` | Configurações SMTP para convites, recuperação de senha e formulário de contato. | Host `mailhog`, porta `1025`, credenciais `podus-*@example.com` |
| `RECAPTCHA_SECRET_KEY`, `RECAPTCHA_SITE_KEY_CHECKBOX`, `RECAPTCHA_SITE_KEY_V3` | Chaves do Google reCAPTCHA (checkbox e v3). | Valores de teste (`testing-*`) |

Copie o bloco `UI_MENUS_JSON` inteiro e edite a estrutura com cuidado para
manter um JSON válido. Utilize aspas simples ao redor do valor na `.env` para
preservar as aspas duplas internas.

## Variáveis opcionais

| Variável | Descrição | Padrão |
| --- | --- | --- |
| `APP_SIDEBAR_TITLE` | Texto exibido na lateral do painel. | `Podus` |
| `UPLOAD_MAX_SIZE_MB` | Limite (em MB) para uploads tratados pelo legado. O helper `LegacyConfig::uploadMaxSizeBytes()` converte para bytes. | `10` |
| `MAIL_*_DEBUG_LEVEL` | Nível de debug do PHPMailer por canal (`invite`, `recovery`, `contact`). | `0` |
| `MAIL_CONTACT_TO_ADDRESS`, `MAIL_CONTACT_CC_ADDRESS`, `MAIL_CONTACT_BCC_ADDRESS` | Destinatários extras do formulário de contato. | `suporte@example.com`, `null`, *(vazio)* |

## Como funciona a camada `LegacyConfig`

1. Ao ser incluído, `config/legacy.php` carrega o `.env` (se existir) e expõe
   métodos como `LegacyConfig::createPdo()`, `LegacyConfig::menuForProfile()` e
   `LegacyConfig::mailConfig('invite')`.
2. Caso alguma variável não esteja definida no ambiente, é utilizado o valor de
   fallback mantido no próprio arquivo (equivalente ao comportamento anterior).
3. Toda a lógica que consumia `conexao.php`, `config.php`, os arrays de menu ou
   credenciais fixas agora consulta `LegacyConfig`, permitindo ajustes via `.env`.

## Passo a passo para configurar um novo ambiente

1. Duplique o `.env.example` para `.env` e preencha as credenciais reais.
2. Revise os blocos de e-mail (`MAIL_INVITE_*`, `MAIL_RECOVERY_*`, `MAIL_CONTACT_*`)
   para apontar aos servidores corretos, ajustando portas, encriptação e remetentes.
3. Atualize as chaves do reCAPTCHA conforme o domínio configurado nas consoles
   do Google.
4. Ajuste o JSON em `UI_MENUS_JSON` se for necessário alterar as entradas exibidas
   para cada perfil.
5. Se desejar alterar textos exibidos na UI, edite `APP_TITLE` e
   `APP_SIDEBAR_TITLE`.

Depois de salvar o `.env`, basta limpar o cache de opcode (se aplicável) ou
reiniciar o servidor web/PHP-FPM para que os novos valores sejam aplicados.
