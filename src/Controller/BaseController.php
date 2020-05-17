<?php namespace MapGuesser\Controller;

abstract class BaseController
{
    protected string $view;

    protected array $variables = [];

    public function render() : string
    {
        $this->operate();

        extract($this->variables);

        ob_start();
        require ROOT . '/views/' . $this->view . '.php';
        $content = ob_get_contents();
        ob_end_clean();

        return $content;
    }

    abstract protected function operate() : void;
}
