<?php
http_response_code(410);
header('Content-Type: application/json; charset=utf-8');

echo json_encode([
    'sucesso' => false,
    'mensagem' => 'O endpoint legacy foi descontinuado. Utilize /api/planning com o parâmetro "acao" correspondente.',
], JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);

exit;
