<?php
define('ROOT_PATH', __DIR__);

use App\Core\Bootstrap;
use App\Core\Container;

// 自动加载
require(ROOT_PATH. '/vendor/autoload.php');
require(ROOT_PATH. '/bootstrap/start.php');

$app = Container::instance();
$bootstrap = new Bootstrap($app);
$bootstrap->run();
