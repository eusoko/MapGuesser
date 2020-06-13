<?php

require 'vendor/autoload.php';

const ROOT = __DIR__;
const VERSION = '';
const REVISION = '';
const REVISION_DATE = '';

$dotenv = Dotenv\Dotenv::createImmutable(ROOT);
$dotenv->load();

class Container
{
    static MapGuesser\Interfaces\Database\IConnection $dbConnection;
    static MapGuesser\Routing\RouteCollection $routeCollection;
    static \SessionHandlerInterface $sessionHandler;
}

Container::$dbConnection = new MapGuesser\Database\Mysql\Connection($_ENV['DB_HOST'], $_ENV['DB_USER'], $_ENV['DB_PASSWORD'], $_ENV['DB_NAME']);
