<?php
/**
 * 前台路由
 */
use App\Controller\Frontend\IndexController;
use App\Controller\Frontend\ExceptionHandler;

$routes = [
    ["GET", "/", [IndexController::class, "index"]]
];

$routeMaps = [
    "/" => $routes
];

return [
    "routes"      => $routeMaps,
    "domains"     => ['study.com'],
    "exception"   => ExceptionHandler::class,
    "allowOrigns" => []
];
