<?php
require 'vendor/autoload.php';

$app = new \Slim\App(['settings' => ['displayErrorDetails' => true]]);
$container = $app->getContainer();
$container['db'] = function () {
    return new PDO("pgsql:host=localhost;port=5432;dbname=appointments", 'user', 'password');
};

require 'routes.php';

$app->run();