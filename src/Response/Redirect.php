<?php namespace MapGuesser\Response;

use MapGuesser\Interfaces\Response\IRedirect;

class Redirect implements IRedirect
{
    private string $target;

    private int $type;

    public function __construct(string $target, int $type = IRedirect::TEMPORARY)
    {
        $this->target = $target;
        $this->type = $type;
    }

    public function getUrl(): string
    {
        if (preg_match('/^http(s)?/', $this->target)) {
            $link = $this->target;
        } else {
            $link = \Container::$request->getBase() . '/' . $this->target;
        }

        return $link;
    }

    public function getHttpCode(): int
    {
        switch ($this->type) {
            case IRedirect::PERMANENT:
                return 301;

            case IRedirect::TEMPORARY:
                return 302;

            default:
                return 302;
        }
    }
}
