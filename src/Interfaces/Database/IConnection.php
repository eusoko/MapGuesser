<?php namespace MapGuesser\Interfaces\Database;

interface IConnection
{
    public function startTransaction(): void;

    public function commit(): void;

    public function rollback(): void;

    public function query(string $query): ?IResultSet;

    public function multiQuery(string $query): array;

    public function prepare(string $query): IStatement;

    public function lastId(): int;

    public function getAffectedRows(): int;
}
