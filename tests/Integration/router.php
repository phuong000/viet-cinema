<?php
declare(strict_types=1);

// Test-only server router outside public/. Never referenced by production bootstrap.
require_once dirname(__DIR__, 2) . '/bootstrap.php';

use App\Core\Application;
use App\Core\Request;
use App\Core\Response;

ini_set('display_errors', '0');
ini_set('error_log', dirname(__DIR__, 2) . '/storage/logs/http-fixture.log');
$router = new App\Core\Router();
$router->add('GET', '/throw', static fn () => throw new RuntimeException('PRIVATE_SENTINEL_DO_NOT_EXPOSE'));
$router->add('GET', '/session', static fn () => Response::json([
    'user' => App\Core\Session::get('user'), 'role' => App\Core\Session::get('role'),
]));
$router->add('GET', '/token', static fn () => Response::json(['token' => App\Core\Csrf::token()]));
foreach (['POST', 'PUT', 'PATCH', 'DELETE'] as $method) {
    $router->add($method, '/change', static fn () => new Response('changed'));
}
if (getenv('VC_TEST_SECURE') === 'true') {
    $_SERVER['HTTPS'] = 'on';
}
(new Application($router))->handle(Request::capture())->send(($_SERVER['REQUEST_METHOD'] ?? '') === 'HEAD');
