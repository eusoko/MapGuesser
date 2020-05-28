<?php namespace MapGuesser\Http;

class Response
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
