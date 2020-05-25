<?php

require '../main.php';

// very basic routing
$host = $_SERVER["REQUEST_SCHEME"] . '://' . $_SERVER["SERVER_NAME"];
$url = $_SERVER['REQUEST_URI'];
if (($pos = strpos($url, '?')) !== false) {
    $url = substr($url, 0, $pos);
}
switch($url) {
    case '/game':
        $controller = new MapGuesser\Controller\GameController();
        break;
    case '/position.json':
        $controller = new MapGuesser\Controller\PositionController();
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
