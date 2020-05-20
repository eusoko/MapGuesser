<?php

require '../main.php';

// very basic routing
$host = $_SERVER["REQUEST_SCHEME"] . '://' . $_SERVER["SERVER_NAME"];
$url = $_SERVER['REQUEST_URI'];
switch($url) {
    case '/game':
        $controller = new MapGuesser\Controller\GameController();
        break;
    case '/getNewPosition.json':
        $controller = new MapGuesser\Controller\GetNewPosition();
        break;
    case '/':
        header('Location: ' . $host  . '/game', true, 302);
        die;
    default:
        echo 'Error 404';
        die;
}

$view = $controller->run();

header('Content-Type: ' . $view->getContentType() . '; charset=UTF-8');

echo $view->render();
