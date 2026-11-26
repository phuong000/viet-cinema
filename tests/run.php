<?php
declare(strict_types=1);

use App\Core\Database;
use App\Core\Request;
use App\Core\Response;
use App\Core\View;

$router = require dirname(__DIR__) . '/bootstrap.php';
require dirname(__DIR__) . '/routes/web.php';
App\Core\Session::start();
$checks = 0;
function check(bool $condition, string $message): void
{
    global $checks;
    if (!$condition) {
        throw new RuntimeException($message);
    }
    $checks++;
    echo "PASS: $message\n";
}
check($router->dispatch(new Request('GET', '/'))->status === 200, 'Home route');
check(str_contains($router->dispatch(new Request('GET', '/'))->body, '<html lang="vi">'), 'MVC renders Vietnamese HTML');
$health = $router->dispatch(new Request('GET', '/health'));
check(json_decode($health->body, true) === ['status' => 'ok'], 'Health response');
check($router->dispatch(new Request('GET', '/missing'))->status === 404, 'Unknown path returns 404');
$denied = $router->dispatch(new Request('POST', '/'));
check($denied->status === 405 && $denied->headers['Allow'] === 'GET, HEAD', 'Method validation and Allow header');
check($router->dispatch(new Request('HEAD', '/'))->status === 200, 'HEAD uses GET route');
check(View::escape('<script>"&') === '&lt;script&gt;&quot;&amp;', 'HTML escaping');
try {
    View::render('../bootstrap');
    check(false, 'View traversal rejected');
} catch (InvalidArgumentException) {
    check(true, 'View traversal rejected');
}
try {
    $router->add('GET', '/', static fn () => new Response('duplicate'));
    check(false, 'Duplicate route rejected');
} catch (LogicException) {
    check(true, 'Duplicate route rejected');
}
$_SERVER['REQUEST_URI'] = '/health?test=1';
$_SERVER['REQUEST_METHOD'] = 'GET';
check(Request::capture()->path === '/health', 'Query string excluded from route');
check(class_exists(Database::class), 'Database adapter loads without connecting');
echo "REGRESSION: $checks/11 checks passed.\n";
require __DIR__ . '/Unit/foundation.php';
echo "TOTAL: $checks checks passed; no database connection was opened.\n";
