<?php namespace MapGuesser\Response;

use MapGuesser\Interfaces\Response\IRedirect;

class Redirect implements IRedirect
{
    private $target;

    private int $type;

    public function __construct($target, int $type = IRedirect::TEMPORARY)
    {
        $this->target = $target;
        $this->type = $type;
    }

    public function getUrl(): string
    {
        if (is_array($this->target)) {
            $link = $this->target[0]->generateLink($this->target[1]);
        } else {
            $link = $this->target;
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
