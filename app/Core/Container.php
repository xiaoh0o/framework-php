<?php
namespace App\Core;

use League\Container\Container as LeagueContainer;
use League\Container\ReflectionContainer;

/**
 * 应用单例
 */
class Container extends LeagueContainer
{
    protected static ?Container $instance = null;

    public function __construct()
    {
        parent::__construct();

        $this->run();
    }

    protected function run()
    {
        // 开启自动注入
        $this->delegate(
            new ReflectionContainer()
        );

        $this->addShared("app", $this);
        $this->addShared(self::class, $this);

        $providers = Configure::get("providers");
        foreach ($providers as $provider) {
            $this->addServiceProvider(new $provider());
        }

        static::$instance = $this;
    }

    public static function instance(): Container
    {
        if (is_null(static::$instance)) {
            new static();
        }
        return static::$instance;
    }

    // 限制复制
    public function __clone()
    {
        throw new \Exception("单例模式，禁止复制！");
    }
}
