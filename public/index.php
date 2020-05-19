<?php

require '../main.php';

// very basic routing
$url = $_SERVER['REQUEST_URI'];
switch($url) {
    case '/':
        $controller = new MapGuesser\Controller\GuessController();
        break;
    case '/getNewPosition.json':
        $controller = new MapGuesser\Controller\GetNewPosition();
        break;
    default:
        echo 'Error 404';
        die;
}

$view = $controller->run();

header('Content-Type: ' . $view->getContentType() . '; charset=UTF-8');

echo $view->render();
