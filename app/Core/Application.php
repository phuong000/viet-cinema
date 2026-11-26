<?php
declare(strict_types=1);

namespace App\Core;

use App\Middleware\VerifyCsrf;

final class Application
{
    public function __construct(private readonly Router $router)
    {
        $router->use(new VerifyCsrf());
    }

    public static function boot(?Router $router = null): self
    {
        require_once dirname(__DIR__, 2) . '/bootstrap.php';
        $router ??= new Router();
        require dirname(__DIR__, 2) . '/routes/web.php';
        return new self($router);
    }

    public function handle(Request $request): Response
    {
        try {
            Session::start();
            if (str_starts_with($request->path, '/assets/')) {
                return $this->asset($request);
            }
            return $this->router->dispatch($request);
        } catch (\Throwable $error) {
            return self::exception($error);
        }
    }

    public static function exception(\Throwable $error): Response
    {
        $status = $error instanceof HttpException ? $error->status : 500;
        if ($status === 500) {
            error_log((string) $error);
        }
        try {
            return Response::error($status);
        } catch (\Throwable $renderError) {
            error_log((string) $renderError);
            return new Response('Request could not be processed.', $status);
        }
    }

    private function asset(Request $request): Response
    {
        $root = realpath(dirname(__DIR__, 2) . '/public/assets');
        $path = realpath(dirname(__DIR__, 2) . '/public' . $request->path);
        $types = ['css' => 'text/css; charset=UTF-8', 'js' => 'text/javascript; charset=UTF-8',
            'png' => 'image/png', 'jpg' => 'image/jpeg', 'jpeg' => 'image/jpeg', 'webp' => 'image/webp'];
        $extension = strtolower(pathinfo($request->path, PATHINFO_EXTENSION));
        if (!$root || !$path || !str_starts_with($path, $root . DIRECTORY_SEPARATOR)
            || !is_file($path) || !isset($types[$extension])) {
            return Response::error(404);
        }
        if (!in_array($request->method, ['GET', 'HEAD'], true)) {
            return Response::error(405, ['Allow' => 'GET, HEAD']);
        }
        return new Response((string) file_get_contents($path), 200, ['Content-Type' => $types[$extension]]);
    }
}
