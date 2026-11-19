<?php
namespace App\Core;

use App\Core\Container;
use App\Core\Http\Request;
use App\Core\Http\Response;
use App\Core\Route\Router;
use App\Exception\ConsoleHandler;
use App\Exception\DefaultHandler;

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
        $request = $this->app->get(Request::class);
        $server = $request->server->all(); // 等效于$_SERVER

        // 路由
        $routeConfig = $this->matchRouter($server['HTTP_HOST']);
        $router = new Router($this->app);
        $router->setRoutes($routeConfig['routes']);
        $router->setAllowOrigins($routeConfig['allowOrigns']);
        $router->setServerParams($server);
        $this->app->addShared("router", $router);
        $this->app->addShared(Router::class, $router);

        // 发送结果
        try {
            $response = $router->run();
        } catch (\Throwable $e) {
            $handler = $routeConfig['exception'] ?? "";
            if (empty($handler)) {
                $handler = DefaultHandler::class;
            }
            $response = (new $handler())->render($e);
        }
        $this->responseResult($response);
    }

    // 输出结果
    protected function responseResult(Response|string|null $response)
    {
        if ($response instanceof Response) {
        } else {
            $headers = [
                "content-type" => "text/html; charset=utf-8"
            ];
            $response = new Response($response, Response::HTTP_OK, $headers);
        }
        $response->send();
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

    // 匹配路由配置
    protected function matchRouter(string $httpHost)
    {
        $configs = Configure::get("router");
        foreach ($configs as $config) {
            if (in_array($httpHost, (array)$config['domains'])) {
                return $config;
            }
        }
        throw new \Exception("Not found router!");
    }
}
