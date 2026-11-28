<?php
declare(strict_types=1);

namespace App\Services\Auth;

interface AccessPolicy
{
    public function authenticated(): bool;

    public function allows(string $permission): bool;
}
