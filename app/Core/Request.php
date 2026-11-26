<?php
declare(strict_types=1);

namespace App\Core;

final class Request
{
    public function __construct(
        public readonly string $method,
        public readonly string $path,
        private readonly array $query = [],
        private readonly array $body = [],
        private readonly array $parameters = [],
    ) {
    }

    public static function capture(): self
    {
        $path = explode('?', $_SERVER['REQUEST_URI'] ?? '/', 2)[0];
        if (!str_starts_with($path, '/') || preg_match('/%(?![a-fA-F0-9]{2})/', $path)) {
            throw new HttpException(400);
        }
        $path = rawurldecode($path);
        if (preg_match('/[\x00-\x20\x7f\\\\]/', $path) || in_array('..', explode('/', $path), true)) {
            throw new HttpException(400);
        }
        $method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
        $body = $_POST;
        if (in_array($method, ['PUT', 'PATCH', 'DELETE'], true)
            && str_starts_with(strtolower($_SERVER['CONTENT_TYPE'] ?? ''), 'application/x-www-form-urlencoded')) {
            parse_str((string) file_get_contents('php://input'), $body);
        }
        return new self($method, $path, $_GET, $body);
    }

    public function input(string $key, string $default = ''): string
    {
        $value = $this->body[$key] ?? $this->query[$key] ?? $default;
        return is_string($value) ? $value : $default;
    }

    public function all(): array
    {
        return $this->body;
    }

    public function parameter(string $key): ?string
    {
        return $this->parameters[$key] ?? null;
    }

    public function withParameters(array $parameters): self
    {
        return new self($this->method, $this->path, $this->query, $this->body, $parameters);
    }
}
