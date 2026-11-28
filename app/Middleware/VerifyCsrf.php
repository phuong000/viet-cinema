<?php
declare(strict_types=1);

namespace App\Middleware;

use App\Core\Csrf;
use App\Core\Request;
use App\Core\Response;

final class VerifyCsrf implements Middleware
{
    public function handle(Request $request, callable $next): Response
    {
        if (!in_array($request->method, ['GET', 'HEAD', 'OPTIONS'], true)) {
            $token = $request->all()['_token'] ?? '';
            if (!is_string($token) || !Csrf::valid($token)) {
                return Response::error(403);
            }
        }
        return $next($request);
    }
}
