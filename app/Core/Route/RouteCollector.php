<?php
namespace App\Core\Route;

/**
 * 路由收集器
 */
class RouteCollector
{
    protected ?RouteParser $routeParser = null; // 解析器
    protected ?DataGenerator $dataGenerator = null;
    protected array $routes = [];
    protected array $patternMatchers = [
        '/{(.+?):number}/'     => '{$1:[0-9]+}',
        '/{(.+?):num}/'        => '{$1:[0-9]+}',
        '/{(.+?):word}/'       => '{$1:[a-zA-Z]+}',
        '/{(.+?):alpha_dash}/' => '{$1:[a-zA-Z0-9-_]+}',
        '/{(.+?):slug}/'       => '{$1:[a-z0-9-]+}',
        '/{(.+?):uuid}/'       => '{$1:[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}+}'
    ];

    public function __construct(
        ?RouteParser $routeParser = null,
        ?DataGenerator $dataGenerator = null
    ) {
        $this->routeParser   = is_null($routeParser)   ? new RouteParser() : $routeParser;
        $this->dataGenerator = is_null($dataGenerator) ? new DataGenerator() : $dataGenerator;
    }

    // 添加路由
    public function addRoute(string $method, string $path, mixed $handler) : Route
    {
        $path = sprintf('/%s', ltrim($path, '/'));
        $route = new Route($method, $path, $handler);
        $this->routes[] = $route;
        return $route;
    }

    // 匹配路由
    public function dispatch(string $method, string $uri)
    {
        // 装载路由到 dataGenerator
        foreach ($this->routes as $route) {
            $routePath = $this->parseRoutePath($route->getPath());
            $routeDatas = $this->routeParser->parse($routePath);
            foreach ($routeDatas as $routeData) {
                $this->dataGenerator->addRoute(
                    $route->getMethod(),
                    $routeData,
                    $route
                );
            }
        }

        $datas = $this->dataGenerator->getData();
        $dispatcher = new Dispatcher($datas);
        $match = $dispatcher->dispatch($method, $uri);

        switch ($match[0]) {
            case Dispatcher::NOT_FOUND:
                throw new RouteException("Route not found!", RouteException::ROUTE_NOT_FOUND);
            case Dispatcher::METHOD_NOT_ALLOWED:
                throw new RouteException("Route method not allowed!", RouteException::METHOD_NOT_ALLOWED);
            case Dispatcher::FOUND:
                if ($match[1] instanceof Route) {
                    $route = $match[1];
                } else {
                    $route = new Route($method, $uri, $match[1]);
                }
                $route->setVars($match[2]);
                return $route;
        }
    }

    protected function parseRoutePath(string $path): string
    {
        return preg_replace(
            array_keys($this->patternMatchers),
            array_values($this->patternMatchers),
            $path
        );
    }

    public function get(string $route, mixed $handler)
    {
        return $this->addRoute('GET', $route, $handler);
    }

    public function post(string $route, mixed $handler)
    {
        return $this->addRoute('POST', $route, $handler);
    }

    public function put(string $route, mixed $handler)
    {
        return $this->addRoute('PUT', $route, $handler);
    }

    public function delete(string $route, mixed $handler)
    {
        return $this->addRoute('DELETE', $route, $handler);
    }

    public function patch(string $route, mixed $handler)
    {
        return $this->addRoute('PATCH', $route, $handler);
    }

    public function head(string $route, mixed $handler)
    {
        return $this->addRoute('HEAD', $route, $handler);
    }

    public function options(string $path, mixed $handler)
    {
        return $this->addRoute('OPTIONS', $path, $handler);
    }
}
