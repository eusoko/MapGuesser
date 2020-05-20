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
