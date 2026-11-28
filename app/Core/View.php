<?php
declare(strict_types=1);

namespace App\Core;

final class View
{
    public static function page(string $name, array $data = [], string $layout = 'main'): string
    {
        $content = self::render($name, $data);
        return self::render('layouts/' . $layout, array_merge($data, ['content' => $content]));
    }

    public static function render(string $name, array $data = []): string
    {
        if (!preg_match('/^[a-zA-Z0-9_\/-]+$/D', $name) || str_contains($name, '..')) {
            throw new \InvalidArgumentException('Invalid view name.');
        }
        $file = dirname(__DIR__) . '/Views/' . $name . '.php';
        if (!is_file($file)) {
            throw new \RuntimeException('View does not exist.');
        }
        ob_start();
        try {
            (static function (string $template, array $values): void {
                extract($values, EXTR_SKIP);
                require $template;
            })($file, $data);
            return (string) ob_get_clean();
        } catch (\Throwable $error) {
            ob_end_clean();
            throw $error;
        }
    }

    public static function escape(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}
