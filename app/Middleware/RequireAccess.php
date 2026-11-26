<?php
declare(strict_types=1);

namespace App\Middleware;

use App\Core\Request;
use App\Core\Response;
use App\Services\Auth\AccessPolicy;

final class RequireAccess implements Middleware
{
    public function __construct(private readonly AccessPolicy $policy, private readonly string $permission)
    {
    }

    public function handle(Request $request, callable $next): Response
    {
        if (!$this->policy->authenticated() || !$this->policy->allows($this->permission)) {
            return Response::error(403);
        }
        return $next($request);
    }
}
