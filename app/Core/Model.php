<?php
declare(strict_types=1);

namespace App\Core;

abstract class Model
{
    public function __construct(private readonly ?\PDO $connection = null)
    {
    }

    protected function query(string $sql, array $parameters = []): \PDOStatement
    {
        $statement = ($this->connection ?? Database::connection())->prepare($sql);
        $statement->execute($parameters);
        return $statement;
    }
}
