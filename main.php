<?php

require 'vendor/autoload.php';

const ROOT = __DIR__;

$dotenv = Dotenv\Dotenv::createImmutable(ROOT);
$dotenv->load();

if (!empty($_ENV['DEV'])) {
    error_reporting(E_ALL);

    ini_set('display_errors', '1');
} else {
    ini_set('display_errors', '0');
}

class Container
{
    static MapGuesser\Interfaces\Database\IConnection $dbConnection;
}

Container::$dbConnection = new MapGuesser\Database\Mysql\Connection($_ENV['DB_HOST'], $_ENV['DB_USER'], $_ENV['DB_PASSWORD'], $_ENV['DB_NAME']);

session_start();
