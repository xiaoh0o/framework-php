<?php
namespace App\Core\Route;

/**
 * 路由
 */
class Route
{
    protected string $method; // 请求方式
    protected string $path; // 路由路径
    protected array  $vars = []; // 变量

    
    /**
     * 处理方法
     *
     * 类型：
     * Closure 闭包函数
     * string  字符串: Controller::method
     * array   数组: [Controller, "method"]
     */
    protected mixed $handler;

    public function __construct(string $method, string $path, mixed $handler, array $vars = [])
    {
        $this->method  = $method;
        $this->handler = $handler;
        $this->path    = $path;
        $this->vars    = $vars;
    }

    public function matches(string $str)
    {
        return (bool) preg_match('~^' . $this->path . '$~', $str);
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function getMethod(): string
    {
        return $this->method;
    }

    public function getVars(): array
    {
        return $this->vars;
    }

    public function setVars(array $vars): self
    {
        $this->vars = $vars;
        return $this;
    }

    public function getHandler()
    {
        return $this->handler;
    }
}
