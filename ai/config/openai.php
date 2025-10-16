<?php
require_once __DIR__ . '/../includes/env.php';
carregarEnv();

return [
    'api_key'  => getenv('OPENAI_API_KEY'),
    'endpoint' => 'https://api.openai.com/v1/chat/completions',
    'model'    => 'gpt-4.1'
];
