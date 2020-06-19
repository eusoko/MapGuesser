<?php namespace MapGuesser\Interfaces\Request;

use MapGuesser\Interfaces\Authentication\IUser;

interface IRequest
{
    public function setParsedRouteParams(array &$routeParams);

    public function getBase(): string;

    public function query(string $key);

    public function post(string $key);

    public function session(): ISession;

    public function setUser(?IUser $user): void;

    public function user(): ?IUser;
}
