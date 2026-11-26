<?php
declare(strict_types=1);

namespace App\Core;

final class Session
{
    public static function cookieOptions(): array
    {
        // Forwarded headers are untrusted until a trusted proxy is configured.
        $https = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' && $_SERVER['HTTPS'] !== '';
        return ['lifetime' => 0, 'path' => '/', 'secure' => $https || getenv('SESSION_SECURE') === 'true',
            'httponly' => true, 'samesite' => 'Lax'];
    }

    public static function start(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            return;
        }
        ini_set('session.use_strict_mode', '1');
        ini_set('session.use_only_cookies', '1');
        $directory = dirname(__DIR__, 2) . '/storage/cache/sessions';
        if (!is_dir($directory) && !mkdir($directory, 0700, true) && !is_dir($directory)) {
            throw new \RuntimeException('Session storage is unavailable.');
        }
        session_save_path($directory);
        session_name('viet_cinema_session');
        session_set_cookie_params(self::cookieOptions());
        if (!session_start()) {
            throw new \RuntimeException('Session could not be started.');
        }
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        self::start();
        return $_SESSION[$key] ?? $default;
    }

    public static function set(string $key, mixed $value): void
    {
        self::start();
        $_SESSION[$key] = $value;
    }

    public static function flash(string $type, string $message): void
    {
        self::set('_flash', ['type' => $type, 'message' => $message]);
    }

    public static function pullFlash(): ?array
    {
        $message = self::get('_flash');
        unset($_SESSION['_flash']);
        return $message;
    }
}
