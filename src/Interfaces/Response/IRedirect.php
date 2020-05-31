<?php namespace MapGuesser\Interfaces\Response;

interface IRedirect
{
    const PERMANENT = 1;

    const TEMPORARY = 2;

    public function getUrl(): string;

    public function getHttpCode(): int;
}
