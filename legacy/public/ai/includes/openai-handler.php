<?php
file_put_contents(__DIR__ . '/erro_ia.log', date('c') . " | INÍCIO\n", FILE_APPEND);
error_reporting(E_ALL);


header('Content-Type: application/json');

require_once __DIR__ . '/../../sessao-professor.php';
require_once __DIR__ . '/../config/openai.php';

$config  = require __DIR__ . '/../config/openai.php';
$apiKey  = $config['api_key'];
$endpoint = $config['endpoint'];
$model   = $config['model'];
file_put_contents(__DIR__ . '/erro_ia.log', "CHAVE: " . $apiKey . "\n", FILE_APPEND);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
	file_put_contents(__DIR__ . '/erro_ia.log', date('c') . " | ERRO: ...\n", FILE_APPEND);

    http_response_code(405);
    echo json_encode(['erro' => 'Método não permitido']);
    exit;
}

file_put_contents(__DIR__ . '/erro_ia.log', date('c') . " | ERRO: ...\n", FILE_APPEND);


$data = json_decode(file_get_contents('php://input'), true);
$prompt = $data['prompt'] ?? '';

if (empty($prompt)) {
	file_put_contents(__DIR__ . '/erro_ia.log', date('c') . " | ERRO: ...\n", FILE_APPEND);

    http_response_code(400);
    echo json_encode(['erro' => 'Prompt não fornecido']);
    exit;
}

$payload = [
    'model' => $model,
    'messages' => [['role' => 'user', 'content' => $prompt]],
    'temperature' => 0.7
];

$ch = curl_init($endpoint);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Authorization: Bearer ' . $apiKey
]);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

if ($error) {
	file_put_contents(__DIR__ . '/erro_ia.log', date('c') . " | ERRO: ...\n", FILE_APPEND);

    http_response_code(500);
    echo json_encode(['erro' => 'Erro ao conectar: ' . $error]);
    exit;
}

if ($httpCode !== 200) {
	file_put_contents(__DIR__ . '/erro_ia.log', date('c') . " | ERRO: ...\n", FILE_APPEND);

    http_response_code($httpCode);
    echo $response;
    exit;
}

$resposta = json_decode($response, true);
$mensagem = $resposta['choices'][0]['message']['content'] ?? '[Sem resposta]';

echo json_encode([
    'sucesso' => true,
    'resposta' => $mensagem
]);
