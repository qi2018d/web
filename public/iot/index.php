<?php
include __DIR__ . '/../common.php';

// define the base directory of the current application
define('APP_ENV', APP_ENV_DEVELOPMENT);
define('APP_DIR', BASE_DIR . '/apps/iot');

$app = new Slim\Slim(array('mode' => APP_ENV));

// load configuration files
$app->configureMode(APP_ENV, function () use ($app) {
        $config = include APP_DIR . '/etc/' . APP_ENV . '.php';
        $password = include APP_DIR . '/etc/' .'password.php';
        $app->config($config);
        $app->config($password);
});

// initialize the routers
$routers = glob(APP_DIR . '/routers/*.router.php');
foreach ($routers as $route) {
    include $route;
}
unset($route, $routers);

session_start();

$app->run();
