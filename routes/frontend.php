<?php
/**
 * 前台路由
 */
use App\Controller\Frontend\Index;
use App\Controller\Frontend\ExceptionHandler;

$routes = [
    ["GET", "/", [Index::class, "index"]]
];

return [
    "domains"     => ['study.com'],
    "exception"   => ExceptionHandler::class,
    "allowOrigns" => [],
    "routes"      => $routes,
];
