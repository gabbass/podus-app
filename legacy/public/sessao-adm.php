<?php

use App\Auth\Exceptions\AuthorizationException;
use App\Auth\LegacySessionGuard;
use App\Auth\Middleware\AdminMiddleware;

require_once dirname(__DIR__, 2) . '/bootstrap/autoload.php';

$guard = LegacySessionGuard::fromGlobals();
$middleware = new AdminMiddleware($guard);

try {
    $middleware->handle($_SERVER, function ($request, $user) use (&$login, &$perfil, &$cliente, &$cpf) {
        $login = $user->login;
        $perfil = $user->profile->value;
        $cliente = $user->school?->clientCode ?? ($_SESSION['cliente'] ?? null);
        $cpf = $_SESSION['cpf'] ?? null;
        $GLOBALS['auth_user'] = $user;

        return true;
    });
} catch (AuthorizationException $exception) {
    echo "<script>alert('É necessário fazer o login na página')</script>";
    echo "<script>location.assign('index-portal')</script>";
    exit;
}