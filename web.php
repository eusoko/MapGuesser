<?php

require 'main.php';

if (!empty($_ENV['DEV'])) {
    error_reporting(E_ALL);

    ini_set('display_errors', '1');
} else {
    ini_set('display_errors', '0');
}

Container::$routeCollection = new MapGuesser\Routing\RouteCollection();

Container::$routeCollection->get('index', '', [MapGuesser\Controller\MapsController::class, 'getMaps']);
Container::$routeCollection->get('startSession', 'startSession.json', [MapGuesser\Controller\HomeController::class, 'startSession']);
Container::$routeCollection->group('login', function (MapGuesser\Routing\RouteCollection $routeCollection) {
    $routeCollection->get('login', '', [MapGuesser\Controller\LoginController::class, 'getLoginForm']);
    $routeCollection->post('login-action', '', [MapGuesser\Controller\LoginController::class, 'login']);
    $routeCollection->get('login-google', 'google', [MapGuesser\Controller\LoginController::class, 'getGoogleLoginRedirect']);
    $routeCollection->get('login-google-action', 'google/code', [MapGuesser\Controller\LoginController::class, 'loginWithGoogle']);
});
Container::$routeCollection->group('signup', function (MapGuesser\Routing\RouteCollection $routeCollection) {
    $routeCollection->get('signup', '', [MapGuesser\Controller\LoginController::class, 'getSignupForm']);
    $routeCollection->post('signup-action', '', [MapGuesser\Controller\LoginController::class, 'signup']);
    $routeCollection->get('signup-google', 'google', [MapGuesser\Controller\LoginController::class, 'getSignupWithGoogleForm']);
    $routeCollection->post('signup-google-action', 'google', [MapGuesser\Controller\LoginController::class, 'signupWithGoogle']);
    $routeCollection->post('signup.reset', 'reset', [MapGuesser\Controller\LoginController::class, 'resetSignup']);
    $routeCollection->post('signup-google.reset', 'google/reset', [MapGuesser\Controller\LoginController::class, 'resetGoogleSignup']);
    $routeCollection->get('signup.success', 'success', [MapGuesser\Controller\LoginController::class, 'getSignupSuccess']);
    $routeCollection->get('signup.activate', 'activate/{token}', [MapGuesser\Controller\LoginController::class, 'activate']);
    $routeCollection->get('signup.cancel', 'cancel/{token}', [MapGuesser\Controller\LoginController::class, 'cancel']);
});
Container::$routeCollection->get('logout', 'logout', [MapGuesser\Controller\LoginController::class, 'logout']);
Container::$routeCollection->group('account', function (MapGuesser\Routing\RouteCollection $routeCollection) {
    $routeCollection->get('account', '', [MapGuesser\Controller\UserController::class, 'getAccount']);
    $routeCollection->post('account-action', '', [MapGuesser\Controller\UserController::class, 'saveAccount']);
});
//Container::$routeCollection->get('maps', 'maps', [MapGuesser\Controller\MapsController::class, 'getMaps']);
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

if (isset($_COOKIE['COOKIES_CONSENT'])) {
    Container::$sessionHandler = new MapGuesser\Session\DatabaseSessionHandler();

    session_set_save_handler(Container::$sessionHandler, true);
    session_start([
        'gc_maxlifetime' => 604800,
        'cookie_lifetime' => 604800,
        'cookie_httponly' => true,
        'cookie_samesite' => 'Lax'
    ]);
} else {
    $_SESSION = [];
}

Container::$request = new MapGuesser\Request\Request($_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'], $_GET, $_POST, $_SESSION);

if (!Container::$request->session()->has('anti_csrf_token')) {
    Container::$request->session()->set('anti_csrf_token', hash('sha256', random_bytes(10) . microtime()));
}
