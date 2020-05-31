<?php namespace MapGuesser\Routing;

class Route
{
    private string $id;

    private array $pattern;

    private array $handler;

    public function __construct(string $id, array $pattern, array $handler)
    {
        $this->id = $id;
        $this->pattern = $pattern;
        $this->handler = $handler;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function generateLink(array $parameters = []): string
    {
        $link = [];

        foreach ($this->pattern as $fragment) {
            if (preg_match('/^{(\\w+)(\\?)?}$/', $fragment, $matches)) {
                if (isset($parameters[$matches[1]])) {
                    $link[] = $parameters[$matches[1]];
                    unset($parameters[$matches[1]]);
                } elseif (!isset($matches[2])) {//TODO: why? parameter not found but not optional
                    $link[] = $fragment;
                }
            } else {
                $link[] = $fragment;
            }
        }

        $queryParams = [];
        foreach ($parameters as $key => $value) {
            if ($value === null) {
                continue;
            }

            $queryParams[$key] = $value;
        }

        $query = count($queryParams) > 0 ? '?' . http_build_query($queryParams) : '';

        return implode('/', $link) . $query;
    }

    public function callController(array $parameters)
    {
        $controllerName = $this->handler[0];
        $controller = new $controllerName();

        return call_user_func([$controller, $this->handler[1]], $parameters);
    }

    public function testAgainst(array $path): ?array
    {
        $parameters = [];

        foreach ($path as $i => $fragment) {
            if (preg_match('/^{(\\w+)(?:\\?)?}$/', $this->pattern[$i], $matches)) {
                $parameters[$matches[1]] = $fragment;
            } elseif ($fragment != $this->pattern[$i]) {
                return null;
            }
        }

        return $parameters;
    }
}
