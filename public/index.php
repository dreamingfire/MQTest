<?php

use App\DistributionApp;

header("Content-Type: text/html; charset=utf-8");

require dirname(__DIR__).'/config/bootstrap.php';

$client = new DistributionApp();
$client->handle();
$client->send();