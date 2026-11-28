<?php
declare(strict_types=1);

use App\Controllers\Admin\DashboardController;
use App\Core\Response;
use App\Middleware\RequireAccess;
use App\Services\Auth\DenyAccess;

// PHUONG: public demo is distinct from the real administration boundary.
$router->add('GET', '/admin/demo', [new DashboardController(), 'demo']);
$router->add('GET', '/admin', static fn (): Response => Response::error(403), [
    new RequireAccess(new DenyAccess(), 'admin.access'),
]);
