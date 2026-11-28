<?php
declare(strict_types=1);

namespace App\Core;

use App\Middleware\Middleware;

final class Router
{
    private array $routes = [];
    private array $middleware = [];

    public function use(Middleware $middleware): void
    {
        $this->middleware[] = $middleware;
    }

    public function add(string $method, string $path, callable $handler, array $middleware = []): void
    {
        $method = strtoupper($method);
        if (!preg_match('~^/(?:[a-zA-Z0-9_/-]|\{[a-zA-Z_][a-zA-Z0-9_]*\})*$~D', $path)) {
            throw new \InvalidArgumentException('Invalid route pattern.');
        }
        preg_match_all('/\{([a-zA-Z_][a-zA-Z0-9_]*)\}/', $path, $matches);
        $names = $matches[1];
        if (count($names) !== count(array_unique($names))) {
            throw new \LogicException('Repeated route parameter.');
        }
        $signature = preg_replace('/\{[^}]+\}/', '{}', $path);
        foreach ($this->routes as $route) {
            if ($route['method'] === $method && $route['signature'] === $signature) {
                throw new \LogicException('Duplicate route: ' . $method . ' ' . $path);
            }
        }
        $regex = preg_quote($path, '~');
        foreach ($names as $name) {
            $regex = str_replace(preg_quote('{' . $name . '}', '~'), '([^/]+)', $regex);
        }
        $this->routes[] = compact('method', 'path', 'handler', 'middleware', 'signature', 'regex', 'names');
    }

    public function dispatch(Request $request): Response
    {
        $matches = [];
        foreach ($this->routes as $route) {
            if (preg_match('~^' . $route['regex'] . '$~D', $request->path, $values)) {
                $route['parameters'] = array_combine($route['names'], array_slice($values, 1));
                $matches[] = $route;
            }
        }
        if ($matches === []) {
            return Response::error(404);
        }
        // Literal paths take precedence, independent of registration order.
        $literal = array_filter($matches, static fn (array $route): bool => $route['names'] === []);
        $matches = $literal ?: $matches;
        $method = $request->method === 'HEAD' ? 'GET' : $request->method;
        foreach ($matches as $route) {
            if ($route['method'] !== $method) {
                continue;
            }
            $next = $route['handler'];
            foreach (array_reverse(array_merge($this->middleware, $route['middleware'])) as $middleware) {
                $handler = $next;
                $next = static fn (Request $request): Response => $middleware->handle($request, $handler);
            }
            return $next($request->withParameters($route['parameters']));
        }
        $allowed = array_values(array_unique(array_column($matches, 'method')));
        if (in_array('GET', $allowed, true)) {
            $allowed[] = 'HEAD';
        }
        return Response::error(405, ['Allow' => implode(', ', $allowed)]);
    }
}
