<?php namespace MapGuesser\View;

class JsonView extends ViewBase
{
    public function __construct(array &$data = [])
    {
        $this->data = &$data;
    }

    public function &render(): string
    {
        $content = json_encode($this->data);

        return $content;
    }

    public function getContentType(): string
    {
        return 'application/json';
    }
}
