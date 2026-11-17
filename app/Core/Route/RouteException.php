<?php
namespace App\Core\Route;

use App\Core\HttpException;

class RouteException extends HttpException
{
    const ROUTE_FOUND        = 200;
    const ROUTE_NOT_FOUND    = 404;
    const METHOD_NOT_ALLOWED = 405;
}
