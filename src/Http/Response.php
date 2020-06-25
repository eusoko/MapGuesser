<?php namespace MapGuesser\Http;

use MapGuesser\Interfaces\Http\IResponse;

class Response implements IResponse
{
    private string $body;

    private array $headers;

    public function __construct(string $body, array $headers)
    {
        $this->body = $body;
        $this->headers = $headers;
    }

    public function getBody()
    {
        return $this->body;
    }

    public function getHeaders()
    {
        return $this->headers;
    }
}
