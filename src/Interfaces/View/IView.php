<?php namespace MapGuesser\Interfaces\View;

interface IView
{
    public function &getData(): array;

    public function &render(): string;

    public function getContentType(): string;
}
