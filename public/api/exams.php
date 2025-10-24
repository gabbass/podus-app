<?php

require_once __DIR__ . '/../../bootstrap/autoload.php';

use App\Auth\Exceptions\AuthorizationException;
use App\Auth\LegacySessionGuard;
use App\Auth\Middleware\AdminOrProfessorMiddleware;
use App\Http\Controllers\Api\ExamController;
use App\Http\JsonResponse;
use App\Http\Request;
use Throwable;

$guard = LegacySessionGuard::fromGlobals();
$middleware = new AdminOrProfessorMiddleware($guard);
$request = Request::fromGlobals();
$controller = new ExamController();

try {
    $response = $middleware->handle($request, function (Request $request, $user) use ($controller) {
        $request->setUser($user);

        return $controller($request);
    });

    if ($response instanceof JsonResponse) {
        $response->send();
        return;
    }

    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
} catch (AuthorizationException $exception) {
    http_response_code(403);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'sucesso' => false,
        'mensagem' => $exception->getMessage(),
    ], JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
} catch (Throwable $exception) {
    http_response_code(500);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'sucesso' => false,
        'mensagem' => 'Erro interno ao processar a solicitação.',
    ], JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
}
