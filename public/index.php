<?php

require '../main.php';

// very basic routing
$url = $_SERVER['REQUEST_URI'];
switch($url) {
    case '/':
        $controller = new MapGuesser\Controller\GuessController();
        break;
    default:
        echo 'Error 404';
        die;
}

echo $controller->render();
