<?php

require '../web.php';

$host = $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'];
$method = strtolower($_SERVER['REQUEST_METHOD']);
$url = substr($_SERVER['REQUEST_URI'], strlen('/'));
if (($pos = strpos($url, '?')) !== false) {
    $url = substr($url, 0, $pos);
}
$url = rawurldecode($url);

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

    if ($method === 'post' && $request->post('anti_csrf_token') !== $request->session()->get('anti_csrf_token')) {
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
            header('Location: ' . $host . '/' . $response->getUrl(), true, $response->getHttpCode());

            return;
        }
    }
}

header('Content-Type: text/html; charset=UTF-8', true, 404);
require ROOT . '/views/error/404.php';
