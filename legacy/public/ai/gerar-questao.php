<?php
/*********************************************************************
 * portal/ai/gerar-questao.php
 * Recebe JSON com { materia, assunto, grau }, chama a OpenAI e devolve
 * JSON com enunciado, alternativas, resposta, justificativa e fonte no formato ABNT.
 * Garante que qualquer dado tabular venha como <table> HTML ou <img>, e não em texto simples.
 *********************************************************************/

header('Content-Type: application/json; charset=utf-8');
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/erro_ia.log');
error_reporting(E_ALL);

// 1) Autenticação de sessão
require_once __DIR__ . '/../sessao-adm-professor.php';

// 2) Carrega config da OpenAI
$config   = require __DIR__ . '/config/openai.php';
$apiKey   = $config['api_key'];
$endpoint = $config['endpoint'];
$model    = $config['model'];

// 3) Lê entrada JSON
$in = json_decode(file_get_contents('php://input'), true);
$mat = trim($in['materia']  ?? '');
$ass = trim($in['assunto']  ?? '');
$gra = trim($in['grau']     ?? '');

// 4) Validação
file_put_contents(__DIR__.'/debug_entrada.log', print_r($in, true), FILE_APPEND);

if ($mat === '' || $ass === '' || $gra === '') {
    http_response_code(400);
    echo json_encode([
        'erro' => 'Parâmetros materia, assunto e grau obrigatórios.'
    ]);
    exit;
}

// 5) Monta prompt, reforçando que tabelas devem vir como HTML ou imagem
$prompt = <<<TXT
Gere UMA questão de múltipla escolha NO FORMATO JSON, baseada em:
Matéria: $mat
Assunto: $ass
Nível de Ensino: $gra

IMPORTANTE:
- Não represente nenhum dado tabular em texto simples.
- Se precisar exibir uma tabela, insira-a como HTML (<table>…</table>).
- Ou, alternativamente, use uma imagem de tabela (<img src="URL_DA_IMAGEM" alt="Tabela">).
- Não inclua descrição em prosa de tabela nem nada fora do JSON.

O JSON de saída deve conter as chaves:
  - enunciado: string (pode conter HTML de tabela ou tag <img>)
  - alternativas: objeto com A, B, C, D e E
  - resposta: uma letra de A a E
  - justificativa: string
  - fonte: string (no formato ABNT: SOBRENOME, Nome. Título. Local: Editora, ano.)

NÃO inclua nada além desse objeto JSON.
TXT;

// 6) Chamada à API da OpenAI
try {
    $payload = [
        'model'       => $model,
        'messages'    => [
            ['role'=>'system','content'=>'Você é um gerador de questões de múltipla escolha.'],
            ['role'=>'user',  'content'=>$prompt]
        ],
        'temperature' => 0.7
    ];

    $ch = curl_init($endpoint);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER     => [
            'Content-Type: application/json',
            'Authorization: Bearer '.$apiKey
        ],
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => json_encode($payload)
    ]);
    $resp     = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $err      = curl_error($ch);
    curl_close($ch);

    if ($err) throw new Exception('Curl: '.$err);
    if ($httpCode !== 200) {
        http_response_code($httpCode);
        echo $resp;
        exit;
    }

    $ja       = json_decode($resp, true);
    $conteudo = $ja['choices'][0]['message']['content'] ?? '';
    $dados    = json_decode($conteudo, true);

    if (!is_array($dados)) {
        throw new Exception('JSON inválido da IA: '.$conteudo);
    }

    // 7) Retorna ao front
    echo json_encode($dados, JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT);

} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode([
        'erro' => 'Falha ao gerar questão: '.$e->getMessage()
    ]);
}
