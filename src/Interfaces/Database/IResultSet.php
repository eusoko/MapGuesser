<?php namespace MapGuesser\Interfaces\Database;

interface IResultSet
{
    const FETCH_ASSOC = 0;

    const FETCH_NUM = 1;

    const FETCH_BOTH = 2;

    public function fetch(int $type);

    public function fetchAll(int $type);

    public function fetchOneColumn(string $valueName, string $keyName);
}
