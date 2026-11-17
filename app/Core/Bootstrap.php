<?php
namespace App\Core;

use App\Core\Container;
use App\Core\Http\Response;
use App\Exception\ConsoleHandler;
use App\Exception\DefaultHandler;
use App\Core\Route\Router;

/**
 * 引导程序
 */
class Bootstrap
{
    public function __construct(
        protected Container $app
    ) {
    }

    public function run()
    {
        if (PHP_SAPI == "cli") {
            $this->cli();
        } else {
            $this->web();
        }
    }

    protected function web()
    {
        session_start();

        // 路由
        $routeConfig = $this->matchRouter();
        $router = new Router($this->app);
        $router->setRoutes($routeConfig['routes']);
        $router->setAllowOrigins($routeConfig['allowOrigns']);
        $router->setServerParams($_SERVER);
        $this->app->addShared("router", $router);
        $this->app->addShared(Router::class, $router);

        $response = new Response();
        $this->app->addShared("response", $response);
        $this->app->addShared(Response::class, $response);

        // 发送结果
        try {
            $response->emit($router->run());
        } catch (\Throwable $e) {
            $handler = $routeConfig['exception'] ?? "";
            if (empty($handler)) {
                $handler = DefaultHandler::class;
            }
            echo (new $handler())->render($e);
        }
    }

    /**
     * CLI 模式
     *
     * 命令：php console.php {$controller}@{$action} ...
     */
    protected function cli()
    {
        try {
            $route = $GLOBALS['argv'][1] ?? [];
            if (!empty($route)) {
                $routes = explode('@', $route);
                $controller = str_replace(".", "\\", $routes[0] ?? "");
                $method = $routes[1] ?? 'run';
            } else {
                $controller = "Index";
                $method = "run";
            }

            $controller = "\\App\\Console\\{$controller}";
            $result = $this->app->get($controller)->$method();
        } catch (\Throwable $e) {
            $result = (new ConsoleHandler())->render($e);
        }

        if (!is_null($result)) {
            echo $result;
        }
    }


    // 匹配路由
    protected function matchRouter()
    {
        $configs = Configure::get("router");
        foreach ($configs as $config) {
            if (in_array($_SERVER["HTTP_HOST"], (array)$config['domains'])) {
                return $config;
            }
        }
        throw new \Exception("Not found router!");
    }
}
