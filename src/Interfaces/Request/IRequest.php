<?php namespace MapGuesser\Interfaces\Request;

use MapGuesser\Interfaces\Authentication\IUser;

interface IRequest
{
    public function query(string $key);

    public function post(string $key);

    public function session(): ISession;

    public function user(): ?IUser;
}
