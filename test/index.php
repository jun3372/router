<?php

include_once __DIR__ . '/../vendor/autoload.php';

$app = \jun3\router\Router::run();
$app->runAction();