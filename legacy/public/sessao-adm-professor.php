<?php

use App\Auth\Exceptions\AuthorizationException;
use App\Auth\LegacySessionGuard;
use App\Auth\Middleware\AdminOrProfessorMiddleware;

require_once dirname(__DIR__, 2) . '/bootstrap/autoload.php';

$guard = LegacySessionGuard::fromGlobals();
$middleware = new AdminOrProfessorMiddleware($guard);

try {
    $middleware->handle($_SERVER, function ($request, $user) use (&$login, &$perfil, &$cliente, &$escola, &$nome, &$id_professor) {
        $session = session();
        $login = $user->login;
        $perfil = $user->profile->value;
        $cliente = $user->school?->clientCode ?? $session->get('cliente');
        $escola = $user->school?->legacyName ?? $session->get('escola');
        $nome = $user->name;
        $id_professor = $user->id ?? $session->get('id');
        $GLOBALS['auth_user'] = $user;

        return true;
    });
} catch (AuthorizationException $exception) {
    header('Location: sair.php');
    exit;
}