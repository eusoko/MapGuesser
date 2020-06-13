<?php

require 'main.php';

if (!empty($_ENV['DEV'])) {
    error_reporting(E_ALL);

    ini_set('display_errors', '1');
} else {
    ini_set('display_errors', '0');
}

Container::$routeCollection = new MapGuesser\Routing\RouteCollection();

Container::$routeCollection->get('index', '', [MapGuesser\Controller\HomeController::class, 'getIndex']);
Container::$routeCollection->get('login', 'login', [MapGuesser\Controller\LoginController::class, 'getLoginForm']);
Container::$routeCollection->post('login-action', 'login', [MapGuesser\Controller\LoginController::class, 'login']);
Container::$routeCollection->get('logout', 'logout', [MapGuesser\Controller\LoginController::class, 'logout']);
Container::$routeCollection->get('maps', 'maps', [MapGuesser\Controller\MapsController::class, 'getMaps']);
Container::$routeCollection->group('game', function (MapGuesser\Routing\RouteCollection $routeCollection) {
    $routeCollection->get('game', '{mapId}', [MapGuesser\Controller\GameController::class, 'getGame']);
    $routeCollection->get('game-json', '{mapId}/json', [MapGuesser\Controller\GameController::class, 'getGameJson']);
    $routeCollection->get('newPlace-json', '{mapId}/newPlace.json', [MapGuesser\Controller\GameFlowController::class, 'getNewPlace']);
    $routeCollection->post('guess-json', '{mapId}/guess.json', [MapGuesser\Controller\GameFlowController::class, 'evaluateGuess']);
});
Container::$routeCollection->group('admin', function (MapGuesser\Routing\RouteCollection $routeCollection) {
    $routeCollection->get('admin.mapEditor', 'mapEditor/{mapId?}', [MapGuesser\Controller\MapAdminController::class, 'getMapEditor']);
    $routeCollection->get('admin.place', 'place.json/{placeId}', [MapGuesser\Controller\MapAdminController::class, 'getPlace']);
    $routeCollection->post('admin.saveMap', 'saveMap/{mapId}/json', [MapGuesser\Controller\MapAdminController::class, 'saveMap']);
});

Container::$sessionHandler = new MapGuesser\Session\DatabaseSessionHandler();

session_set_save_handler(Container::$sessionHandler, true);
session_start([
    'gc_maxlifetime' => 604800,
    'cookie_lifetime' => 604800,
    'cookie_httponly' => true,
    'cookie_samesite' => 'Lax'
]);
