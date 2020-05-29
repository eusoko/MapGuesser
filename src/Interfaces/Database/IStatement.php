<?php namespace MapGuesser\Interfaces\Database;

interface IStatement
{
    public function execute(array $params): ?IResultSet;

    public function getAffectedRows(): int;
}
