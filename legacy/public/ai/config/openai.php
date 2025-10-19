<?php
require_once dirname(__DIR__, 3) . '/includes/env.php';
carregarEnv();

return [
    'api_key'  => getenv('OPENAI_API_KEY'),
    'endpoint' => 'https://api.openai.com/v1/chat/completions',
    'model'    => 'gpt-4.1'
];
