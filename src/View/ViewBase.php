<?php namespace MapGuesser\View;

abstract class ViewBase
{
    protected array $data;

    public function &getData(): array
    {
        return $this->data;
    }

    abstract public function &render(): string;

    abstract public function getContentType(): string;
}
