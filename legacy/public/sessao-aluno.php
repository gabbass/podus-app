<?php

use App\Auth\Exceptions\AuthorizationException;
use App\Auth\LegacySessionGuard;
use App\Auth\Middleware\AlunoMiddleware;

require_once dirname(__DIR__, 2) . '/bootstrap/autoload.php';

$guard = LegacySessionGuard::fromGlobals();
$middleware = new AlunoMiddleware($guard);

try {
    $middleware->handle($_SERVER, function ($request, $user) use (&$login, &$perfil, &$escola, &$nome, &$turma) {
        $session = $GLOBALS['_SESSION'] ?? $_SESSION ?? [];
        if (empty($session['matricula'])) {
            throw new AuthorizationException('Matrícula não localizada.');
        }

        $login = $user->login;
        $perfil = $user->profile->value;
        $escola = $user->school?->legacyName ?? ($session['escola'] ?? '');
        $nome = $user->name ?: ($session['nome'] ?? '');
        $turma = $session['turma'] ?? null;
        $GLOBALS['auth_user'] = $user;

        return true;
    });
} catch (AuthorizationException $exception) {
    echo "<script>alert('É necessário fazer o login na página');</script>";
    echo "<script>window.location.href = 'login-aluno';</script>";
    exit;
}
