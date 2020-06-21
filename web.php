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
Container::$routeCollection->get('login-google', 'login/google', [MapGuesser\Controller\LoginController::class, 'getGoogleLoginRedirect']);
Container::$routeCollection->get('login-google-action', 'login/google/code', [MapGuesser\Controller\LoginController::class, 'loginWithGoogle']);
Container::$routeCollection->get('signup', 'signup', [MapGuesser\Controller\LoginController::class, 'getSignupForm']);
Container::$routeCollection->post('signup-action', 'signup', [MapGuesser\Controller\LoginController::class, 'signup']);
Container::$routeCollection->get('signup-google', 'signup/google', [MapGuesser\Controller\LoginController::class, 'getSignupWithGoogleForm']);
Container::$routeCollection->post('signup-google-action', 'signup/google', [MapGuesser\Controller\LoginController::class, 'signupWithGoogle']);
Container::$routeCollection->get('signup.success', 'signup/success', [MapGuesser\Controller\LoginController::class, 'getSignupSuccess']);
Container::$routeCollection->get('signup.activate', 'signup/activate/{token}', [MapGuesser\Controller\LoginController::class, 'activate']);
Container::$routeCollection->get('signup.cancel', 'signup/cancel/{token}', [MapGuesser\Controller\LoginController::class, 'cancel']);
Container::$routeCollection->get('logout', 'logout', [MapGuesser\Controller\LoginController::class, 'logout']);
Container::$routeCollection->get('profile', 'profile', [MapGuesser\Controller\UserController::class, 'getProfile']);
Container::$routeCollection->post('profile-action', 'profile', [MapGuesser\Controller\UserController::class, 'saveProfile']);
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
    $routeCollection->post('admin.deleteMap', 'deleteMap/{mapId}', [MapGuesser\Controller\MapAdminController::class, 'deleteMap']);
});

Container::$sessionHandler = new MapGuesser\Session\DatabaseSessionHandler();

session_set_save_handler(Container::$sessionHandler, true);
session_start([
    'gc_maxlifetime' => 604800,
    'cookie_lifetime' => 604800,
    'cookie_httponly' => true,
    'cookie_samesite' => 'Lax'
]);

Container::$request = new MapGuesser\Request\Request($_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'], $_GET, $_POST, $_SESSION);

if (!Container::$request->session()->has('anti_csrf_token')) {
    Container::$request->session()->set('anti_csrf_token', hash('sha256', random_bytes(10) . microtime()));
}
