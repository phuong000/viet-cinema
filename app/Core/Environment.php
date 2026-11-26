<?php
declare(strict_types=1);

namespace App\Core;

final class Environment
{
    public static function load(string $path): void
    {
        if (!is_file($path)) {
            return;
        }
        foreach (file($path, FILE_IGNORE_NEW_LINES) as $line) {
            $line = trim($line);
            if ($line === '' || str_starts_with($line, '#')) {
                continue;
            }
            if (!preg_match('/^([A-Z_][A-Z0-9_]*)=(.*)$/', $line, $matches)) {
                throw new \RuntimeException('Invalid environment configuration.');
            }
            if (getenv($matches[1]) === false) {
                putenv($matches[1] . '=' . trim($matches[2], "\"'"));
            }
        }
    }
}
