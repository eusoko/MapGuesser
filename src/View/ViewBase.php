<?php namespace MapGuesser\View;

use MapGuesser\Interfaces\View\IView;

abstract class ViewBase implements IView
{
    protected array $data;

    public function &getData(): array
    {
        return $this->data;
    }

    abstract public function &render(): string;

    abstract public function getContentType(): string;
}
