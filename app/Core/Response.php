<?php
declare(strict_types=1);

namespace App\Core;

final class Response
{
    public function __construct(
        public readonly string $body,
        public readonly int $status = 200,
        public readonly array $headers = ['Content-Type' => 'text/html; charset=UTF-8'],
    ) {
    }

    public static function json(array $data, int $status = 200): self
    {
        return new self(json_encode($data, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE), $status,
            ['Content-Type' => 'application/json; charset=UTF-8']);
    }

    public static function error(int $status, array $headers = []): self
    {
        $titles = [400 => 'Bad Request', 403 => 'Forbidden', 404 => 'Not Found',
            405 => 'Method Not Allowed', 500 => 'Internal Server Error'];
        $title = $titles[$status] ?? 'Request Error';
        return new self(View::page('errors/http', compact('status', 'title')), $status,
            ['Content-Type' => 'text/html; charset=UTF-8'] + $headers);
    }

    public function send(bool $head = false): void
    {
        http_response_code($this->status);
        foreach ($this->headers as $name => $value) {
            header($name . ': ' . $value);
        }
        header('X-Content-Type-Options: nosniff');
        header('Referrer-Policy: strict-origin-when-cross-origin');
        header("Content-Security-Policy: default-src 'self'; style-src 'self'; script-src 'self'; img-src 'self'; base-uri 'none'; frame-ancestors 'none'; form-action 'self'");
        if (!$head) {
            echo $this->body;
        }
    }
}
