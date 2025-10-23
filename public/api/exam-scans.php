<?php

require_once __DIR__ . '/../../bootstrap/autoload.php';

use App\Http\Controllers\ExamCorrectionController;
use App\Http\Request;

$request = Request::fromGlobals();
$controller = new ExamCorrectionController();
$response = $controller($request);

if (method_exists($response, 'send')) {
    $response->send();
    return;
}

echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
