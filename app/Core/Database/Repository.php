<?php
namespace App\Core\Database;

/**
 * 仓储基类
 */
abstract class Repository
{
    protected string $table;
    protected DatabaseInterface $db;

    public function __construct()
    {
        $this->setDriver("default");
    }

    // 设置当前数据库
    public function setDriver(string $name): void
    {
        $this->db = Database::instance($name);
    }

    // 获取数据库连接
    public function driver(): DatabaseInterface
    {
        return $this->db;
    }

    // 获取数据库表
    public function table(): string
    {
        return $this->table;
    }
}
