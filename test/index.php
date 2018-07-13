<?php

include_once __DIR__ . '/../vendor/autoload.php';

$app = \jun\router\Router::run();
$app->runAction();