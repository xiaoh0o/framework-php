<?php
use App\Core\Container;
use Nette\Http\Request;

// 定义方法


function app(string $name = "")
{
    if ($name == "") {
        return Container::instance();
    }
    return Container::instance()->get($name);
}

function request()
{
    return app(Request::class);
}
