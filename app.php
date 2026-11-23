<?php
define('ROOT_PATH', __DIR__);

use App\Core\Bootstrap;
use App\Core\Container;

// 自动加载
require(ROOT_PATH. '/vendor/autoload.php');
require(ROOT_PATH. '/bootstrap/ready.php');

$app = new Container();
$bootstrap = new Bootstrap($app);
$bootstrap->run();
