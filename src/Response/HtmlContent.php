<?php namespace MapGuesser\Response;

class HtmlContent extends ContentBase
{
    private string $template;

    public function __construct(string $template, array &$data = [])
    {
        $this->template = $template;
        $this->data = &$data;
    }

    public function &render(): string
    {
        extract($this->data);

        ob_start();
        require ROOT . '/views/' . $this->template . '.php';
        $content = ob_get_contents();
        ob_end_clean();

        return $content;
    }

    public function getContentType(): string
    {
        return 'text/html';
    }
}
