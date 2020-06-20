<?php namespace MapGuesser\Util;

class JwtParser
{
    private array $token;

    public function __construct(?string $token = null)
    {
        if ($token !== null) {
            $this->setToken($token);
        }
    }

    public function setToken(string $token)
    {
        $this->token = explode('.', str_replace(['_', '-'], ['/', '+'], $token));
    }

    public function getHeader(): array
    {
        return json_decode(base64_decode($this->token[0]), true);
    }

    public function getPayload(): array
    {
        return json_decode(base64_decode($this->token[1]), true);
    }

    public function getSignature(): string
    {
        return base64_decode($this->token[2]);
    }
}
