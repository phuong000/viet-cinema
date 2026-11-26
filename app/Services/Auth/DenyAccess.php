<?php
declare(strict_types=1);

namespace App\Services\Auth;

final class DenyAccess implements AccessPolicy
{
    public function authenticated(): bool
    {
        return false;
    }

    public function allows(string $permission): bool
    {
        return false;
    }
}
