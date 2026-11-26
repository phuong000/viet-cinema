<?php
declare(strict_types=1);

// Reference only; Database reads these environment variables lazily.
return [
    'dsn' => getenv('DB_DSN') ?: 'mysql:host=127.0.0.1;port=3306;dbname=viet_cinema;charset=utf8mb4',
    'username' => getenv('DB_USERNAME') ?: '',
    'password' => getenv('DB_PASSWORD') ?: '',
];
