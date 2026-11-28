<?php
declare(strict_types=1);

use App\Controllers\HomeController;
use App\Core\Response;
use App\Core\Router;

/** @var Router $router */
$router->add('GET', '/', [new HomeController(), 'index']);
$router->add('GET', '/health', static fn (): Response => Response::json(['status' => 'ok']));

foreach (['auth', 'movies', 'booking', 'admin'] as $module) {
    require __DIR__ . '/' . $module . '.php';
}
