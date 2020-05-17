<?php

require 'vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

if (!empty($_ENV['DEV'])) {
    error_reporting(E_ALL);

    ini_set('display_errors', '1');
} else {
    ini_set('display_errors', '0');
}
