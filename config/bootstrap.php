<?php

use Dotenv\Dotenv;
use Dotenv\Environment\DotenvFactory;
use Dotenv\Loader;

require dirname(__DIR__).'/vendor/autoload.php';

// load all the .env files
(new Dotenv(new Loader([dirname(__DIR__).'/.env'],new DotenvFactory())))->load();

$_SERVER['APP_ENV'] = $_ENV['APP_ENV'] = ($_SERVER['APP_ENV'] ?? $_ENV['APP_ENV'] ?? null) ?: 'dev';
$_SERVER['APP_DEBUG'] = $_SERVER['APP_DEBUG'] ?? $_ENV['APP_DEBUG'] ?? 'prod' !== $_SERVER['APP_ENV'];
$_SERVER['APP_DEBUG'] = $_ENV['APP_DEBUG'] = (int) $_SERVER['APP_DEBUG'] || filter_var($_SERVER['APP_DEBUG'], FILTER_VALIDATE_BOOLEAN) ? '1' : '0';