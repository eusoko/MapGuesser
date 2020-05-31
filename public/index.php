<?php

require '../main.php';

// very basic routing
$host = $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['SERVER_NAME'];
$method = strtolower($_SERVER['REQUEST_METHOD']);
$url = substr($_SERVER['REQUEST_URI'], strlen('/'));
if (($pos = strpos($url, '?')) !== false) {
    $url = substr($url, 0, $pos);
}
$url = rawurldecode($url);

Container::$routeCollection->get('index', '', [MapGuesser\Controller\HomeController::class, 'getIndex']);
Container::$routeCollection->get('maps', 'maps', [MapGuesser\Controller\MapsController::class, 'getMaps']);
Container::$routeCollection->group('game', function (MapGuesser\Routing\RouteCollection $routeCollection) {
    $routeCollection->get('game', '{mapId}', [MapGuesser\Controller\GameController::class, 'getGame']);
    $routeCollection->get('game-json', '{mapId}/json', [MapGuesser\Controller\GameController::class, 'getGameJson']);
    $routeCollection->get('position-json', '{mapId}/position.json', [MapGuesser\Controller\PositionController::class, 'getPosition']);
    $routeCollection->post('guess-json', '{mapId}/guess.json', [MapGuesser\Controller\PositionController::class, 'evaluateGuess']);
});

$match = Container::$routeCollection->match($method, explode('/', $url));

if ($match !== null) {
    list($route, $params) = $match;

    $response = $route->callController($params);

    if ($response instanceof MapGuesser\Interfaces\Response\IContent) {
        header('Content-Type: ' . $response->getContentType() . '; charset=UTF-8');
        echo $response->render();
    } elseif ($response instanceof MapGuesser\Interfaces\Response\IRedirect) {
        header('Location: ' . $host . '/' . $response->getUrl(), true, $response->getHttpCode());
    }
} else {
    header('Content-Type: text/html; charset=UTF-8', true, 404);
    require ROOT . '/views/error/404.php';
}
