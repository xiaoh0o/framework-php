<?php
namespace App\Core\Route;

use App\Core\Container;

/**
 * 路由管理
 */
class Router
{
    protected array $routes = [];       // 路由配置表
    protected array $allowOrigns = [];
    protected array $serverParams = [];
    protected Route $route;

    public function __construct(
        protected Container $app
    ) {
    }

    public function setRoutes(array $routes)
    {
        $this->routes = $routes;
    }

    // 设置 CORS 准许通过的来源
    public function setAllowOrigins(array $allowOrigns)
    {
        $this->allowOrigns = $allowOrigns;
    }

    public function setServerParams(array $serverParams)
    {
        $this->serverParams = $serverParams;
    }

    // 解析路由
    public function run()
    {
        $serverParams = $this->serverParams ?: ($_SERVER ?? []);

        // 解析当前请求的数据
        $uri = parse_url($serverParams['REQUEST_URI'] ?? "/", PHP_URL_PATH);
        $uri = rtrim($uri, "/");
        $uri = $uri == "" ? "/" : $uri;
        $httpMethod = $serverParams['REQUEST_METHOD'] ?? "";

        // 跨域请求处理
        $this->corsHandle($httpMethod);

        $router = new RouteCollector();

        // 加载路由配置
        foreach ($this->routes as $item) {
            $methods = (array)$item[0];
            $path    = $item[1];
            $handler = $item[2];
            foreach ($methods as $method) {
                $router->addRoute($method, $path, $handler);
            }
        }

        // 匹配路由
        $route = $router->dispatch($httpMethod, $uri);

        // 保存路由信息
        $this->route = $route;

        // 执行处理（调用控制器的方法）
        return $this->handle($route);
    }

    // 控制器调用
    private function handle(Route $route)
    {
        $handler = $route->getHandler();
        $vars    = $route->getVars();

        // 闭包
        if ($handler instanceof \Closure) {
            return $handler($vars);
        }

        // 数组
        if (is_array($handler)) {
            $controller = $handler[0];
            $method     = $handler[1] ?? "index";
        }

        // 字符串
        elseif (is_string($handler)) {
            if (strpos($handler, "@")) {
                $array = explode("@", $handler);
            } else {
                $array = explode("::", $handler);
            }
            $length = count($array);
            if ($length == 1) {
                $controller = $array[0];
                $method     = "index";
            } elseif ($length == 2) {
                $controller = $array[0];
                $method     = $array[1] ?: "index";
            } else {
                throw new RouteException("Route handler error!");
            }
        } else {
            throw new RouteException("Route handler error!");
        }

        if (is_object($controller)) {
            return $controller->$method($vars);
        } else {
            return $this->app->get($controller)->$method($vars);
        }
    }

    public function getRoute()
    {
        return $this->route;
    }

    /**
     * 跨域请求处理
     *
     * 完成 跨域请求，并携带 cookie 返回，必须完成 以下三点：
     * 1. 前端请求时 配置 "withCredentials": true
     * 2. 后端返回时 设置 Access-Control-Allow-Credentials: true
     * 3. 后端返回时 设置 Access-Control-Allow-Origin: https://...
     */
    protected function corsHandle(string $httpMethod)
    {
        $origin = $_SERVER['HTTP_ORIGIN'] ?? "*";
        if (empty($this->allowOrigns)) {
            header("Access-Control-Allow-Origin: {$origin}");
        } else {
            if (in_array($origin, $this->allowOrigns)) {
                header("Access-Control-Allow-Origin: {$origin}");
            }
        }
        header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept, Authorization");
        header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
        header("Access-Control-Allow-Credentials: true");
        if ($httpMethod == "OPTIONS") {
            header('HTTP/1.1 200 OK');
            exit();
        }
    }
}
