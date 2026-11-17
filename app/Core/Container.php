<?php
namespace App\Core;

use League\Container\Container as LeagueContainer;
use League\Container\ReflectionContainer;
use App\Core\Configure;

/**
 * 应用单例
 */
class Container extends LeagueContainer
{
    protected static ?Container $instance = null;

    public function __construct()
    {
        parent::__construct();

        // 开启自动注入
        $this->delegate(
            new ReflectionContainer()
        );

        $this->addShared("app", $this);
        $this->addShared(self::class, $this);

        static::$instance = $this;
    }

    public static function instance(): Container
    {
        if (is_null(static::$instance)) {
            $providers = Configure::get("providers");
            $app = new self();
            foreach ($providers as $provider) {
                $app->addServiceProvider(new $provider());
            }
        }
        return static::$instance;
    }

    // 限制复制
    public function __clone()
    {
        throw new \Exception("单例模式，禁止复制！");
    }
}
