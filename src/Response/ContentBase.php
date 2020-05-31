<?php namespace MapGuesser\Response;

use MapGuesser\Interfaces\Response\IContent;

abstract class ContentBase implements IContent
{
    protected array $data;

    public function &getData(): array
    {
        return $this->data;
    }

    abstract public function &render(): string;

    abstract public function getContentType(): string;
}
