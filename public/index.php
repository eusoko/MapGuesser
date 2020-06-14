<?php

require '../web.php';

$method = strtolower($_SERVER['REQUEST_METHOD']);
$url = substr($_SERVER['REQUEST_URI'], strlen('/'));
if (($pos = strpos($url, '?')) !== false) {
    $url = substr($url, 0, $pos);
}
$url = rawurldecode($url);

$match = Container::$routeCollection->match($method, explode('/', $url));

if ($match !== null) {
    list($route, $params) = $match;

    Container::$request->setParsedRouteParams($params);

    $handler = $route->getHandler();
    $controller = new $handler[0](Container::$request);

    if ($controller instanceof MapGuesser\Interfaces\Authorization\ISecured) {
        $authorized = $controller->authorize();
    } else {
        $authorized = true;
    }

    if ($method === 'post' && Container::$request->post('anti_csrf_token') !== Container::$request->session()->get('anti_csrf_token')) {
        header('Content-Type: text/html; charset=UTF-8', true, 403);
        echo json_encode(['error' => 'no_valid_anti_csrf_token']);
        return;
    }

    if ($authorized) {
        $response = call_user_func([$controller, $handler[1]]);

        if ($response instanceof MapGuesser\Interfaces\Response\IContent) {
            header('Content-Type: ' . $response->getContentType() . '; charset=UTF-8');
            echo $response->render();

            return;
        } elseif ($response instanceof MapGuesser\Interfaces\Response\IRedirect) {
            header('Location: ' . Container::$request->getBase() . '/' . $response->getUrl(), true, $response->getHttpCode());

            return;
        }
    }
}

header('Content-Type: text/html; charset=UTF-8', true, 404);
require ROOT . '/views/error/404.php';
