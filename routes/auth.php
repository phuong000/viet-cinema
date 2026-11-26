<?php
declare(strict_types=1);

use App\Controllers\Auth\AuthController;

// PHUONG: forms only; no identity is created by these placeholders.
$auth = new AuthController();
foreach (['GET', 'POST'] as $method) {
    $router->add($method, '/login', [$auth, 'login']);
    $router->add($method, '/register', [$auth, 'register']);
}
