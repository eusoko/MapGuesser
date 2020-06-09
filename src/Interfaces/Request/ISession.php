<?php namespace MapGuesser\Interfaces\Request;

interface ISession
{
    public function has(string $key): bool;

    public function get(string $key);

    public function set(string $key, $value): void;

    public function delete(string $key): void;
}
