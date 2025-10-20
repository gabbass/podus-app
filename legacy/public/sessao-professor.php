<?php

use App\Auth\Exceptions\AuthorizationException;
use App\Auth\LegacySessionGuard;
use App\Auth\Middleware\ProfessorMiddleware;

require_once dirname(__DIR__, 2) . '/bootstrap/autoload.php';

$guard = LegacySessionGuard::fromGlobals();
$middleware = new ProfessorMiddleware($guard);

try {
    $middleware->handle($_SERVER, function ($request, $user) use (&$login, &$perfil, &$escola, &$nome, &$id_professor) {
        $login = $user->login;
        $perfil = $user->profile->value;
        $escola = $user->school?->legacyName ?? ($_SESSION['escola'] ?? null);
        $nome = $user->name;
        $id_professor = $user->id ?? ($_SESSION['id'] ?? null);
        $GLOBALS['auth_user'] = $user;

        return true;
    });
} catch (AuthorizationException $exception) {
    header('Location: sair.php');
    exit;
}