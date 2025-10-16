<?php
function carregarEnv($caminho = __DIR__ . '/../.env') {
    if (!file_exists($caminho)) {
        file_put_contents(__DIR__ . '/erro_ia.log', date('c') . " | .env NÃO encontrado em: $caminho\n", FILE_APPEND);
        return;
    }

    $linhas = file($caminho, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($linhas as $linha) {
        if (strpos(trim($linha), '#') === 0) continue;

        list($chave, $valor) = array_map('trim', explode('=', $linha, 2));
        putenv("$chave=$valor");
        $_ENV[$chave] = $valor;
    }

    file_put_contents(__DIR__ . '/erro_ia.log', date('c') . " | .env carregado com sucesso\n", FILE_APPEND);
}
