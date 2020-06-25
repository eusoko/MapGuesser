<?php namespace MapGuesser\Interfaces\Http;

interface IRequest
{
    const HTTP_GET = 0;

    const HTTP_POST = 1;

    public function setUrl(string $url): void;

    public function setMethod(int $method): void;

    public function setQuery($query);

    public function setHeaders(array $headers);

    public function send(): IResponse;
}
