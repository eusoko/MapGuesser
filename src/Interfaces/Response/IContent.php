<?php namespace MapGuesser\Interfaces\Response;

interface IContent
{
    public function &getData(): array;

    public function &render(): string;

    public function getContentType(): string;
}
