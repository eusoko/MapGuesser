<?php

require '../main.php';

// very basic routing
$host = $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'];
$method = strtolower($_SERVER['REQUEST_METHOD']);
$url = substr($_SERVER['REQUEST_URI'], strlen('/'));
if (($pos = strpos($url, '?')) !== false) {
    $url = substr($url, 0, $pos);
}
$url = rawurldecode($url);

Container::$routeCollection->get('index', '', [MapGuesser\Controller\HomeController::class, 'getIndex']);
Container::$routeCollection->get('login', 'login', [MapGuesser\Controller\LoginController::class, 'getLoginForm']);
Container::$routeCollection->post('login-action', 'login', [MapGuesser\Controller\LoginController::class, 'login']);
Container::$routeCollection->get('logout', 'logout', [MapGuesser\Controller\LoginController::class, 'logout']);
Container::$routeCollection->get('maps', 'maps', [MapGuesser\Controller\MapsController::class, 'getMaps']);
Container::$routeCollection->group('game', function (MapGuesser\Routing\RouteCollection $routeCollection) {
    $routeCollection->get('game', '{mapId}', [MapGuesser\Controller\GameController::class, 'getGame']);
    $routeCollection->get('game-json', '{mapId}/json', [MapGuesser\Controller\GameController::class, 'getGameJson']);
    $routeCollection->get('position-json', '{mapId}/position.json', [MapGuesser\Controller\PositionController::class, 'getPosition']);
    $routeCollection->post('guess-json', '{mapId}/guess.json', [MapGuesser\Controller\PositionController::class, 'evaluateGuess']);
});
Container::$routeCollection->group('admin', function (MapGuesser\Routing\RouteCollection $routeCollection) {
    $routeCollection->get('admin.maps', 'maps', [MapGuesser\Controller\MapAdminController::class, 'getMaps']);
    $routeCollection->get('admin.mapEditor', 'mapEditor/{mapId}', [MapGuesser\Controller\MapAdminController::class, 'getMapEditor']);
    $routeCollection->get('admin.place', 'place.json/{placeId}', [MapGuesser\Controller\MapAdminController::class, 'getPlace']);
});

$match = Container::$routeCollection->match($method, explode('/', $url));

if ($match !== null) {
    list($route, $params) = $match;

    $request = new MapGuesser\Request\Request($_GET, $params, $_POST, $_SESSION);

    $handler = $route->getHandler();
    $controller = new $handler[0]($request);

    if ($controller instanceof MapGuesser\Interfaces\Authorization\ISecured) {
        $authorized = $controller->authorize();
    } else {
        $authorized = true;
    }

    if ($authorized) {
        $response = call_user_func([$controller, $handler[1]]);

        if ($response instanceof MapGuesser\Interfaces\Response\IContent) {
            header('Content-Type: ' . $response->getContentType() . '; charset=UTF-8');
            echo $response->render();

            return;
        } elseif ($response instanceof MapGuesser\Interfaces\Response\IRedirect) {
            header('Location: ' . $host . '/' . $response->getUrl(), true, $response->getHttpCode());

            return;
        }
    }
}

header('Content-Type: text/html; charset=UTF-8', true, 404);
require ROOT . '/views/error/404.php';
