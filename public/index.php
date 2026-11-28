<?php
declare(strict_types=1);

use App\Core\Application;
use App\Core\Request;

ini_set('display_errors', '0');
error_reporting(E_ALL);
try {
    $router = require dirname(__DIR__) . '/bootstrap.php';
    ini_set('log_errors', '1');
    ini_set('error_log', dirname(__DIR__) . '/storage/logs/application.log');
    $application = Application::boot($router);
    $request = Request::capture();
    $application->handle($request)->send($request->method === 'HEAD');
} catch (Throwable $error) {
    if (class_exists(Application::class)) {
        Application::exception($error)->send(($_SERVER['REQUEST_METHOD'] ?? '') === 'HEAD');
    } else {
        error_log((string) $error);
        http_response_code(500);
        if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'HEAD') {
            echo 'Internal Server Error';
        }
    }
}
