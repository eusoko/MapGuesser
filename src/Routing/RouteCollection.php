<?php namespace MapGuesser\Routing;

use Closure;

class RouteCollection
{
    private array $routes = [];

    private array $searchTable = [
        'get' => [],
        'post' => []
    ];

    private array $groupStack = [];

    public function get(string $id, string $pattern, array $handler): void
    {
        $this->addRoute('get', $id, $pattern, $handler);
    }

    public function post(string $id, string $pattern, array $handler): void
    {
        $this->addRoute('post', $id, $pattern, $handler);
    }

    public function group(string $pattern, Closure $group): void
    {
        $this->groupStack[] = $pattern;

        $group($this);

        array_pop($this->groupStack);
    }

    public function getRoute(string $id): ?Route
    {
        if (!isset($this->routes[$id])) {
            return null;
        }

        return $this->routes[$id];
    }

    public function match(string $method, array $uri): ?array
    {
        $groupNumber = count($uri);

        if (!isset($this->searchTable[$method][$groupNumber])) {
            return null;
        }

        foreach ($this->searchTable[$method][$groupNumber] as $route) {
            if (($parameters = $route->testAgainst($uri)) !== null) {
                return [$route, $parameters];
            }
        }

        return null;
    }

    private function addRoute(string $method, string $id, string $pattern, array $handler): void
    {
        if (isset($this->routes[$id])) {
            throw new \Exception('Route already exists: ' . $id);
        }

        $pattern = array_merge($this->groupStack, $pattern === '' ? [] : explode('/', $pattern));
        $route = new Route($id, $pattern, $handler);

        $groupNumber = count($pattern);

        $this->searchTable[$method][$groupNumber][] = $route;

        while (preg_match('/^{\\w+\\?}$/', end($pattern))) {
            $groupNumber--;
            array_pop($pattern);

            $this->searchTable[$method][$groupNumber][] = $route;
        }

        $this->routes[$id] = $route;
    }
}
