<?php
namespace App\Core\Database;

/**
 * 数据库事务
 */
class Transaction implements TransactionInterface
{
    protected DatabaseInterface $driver;

    /**
     * 因不支持嵌套事务，该变量记录开启事务的层数
     */
    protected static $transactions = 0;

    public function __construct(DatabaseInterface $driver)
    {
        $this->driver = $driver;
    }

    public function begin()
    {
        static::$transactions += 1;
        if (static::$transactions == 1) {
            $this->driver->query("START TRANSACTION");
        }
    }

    public function commit()
    {
        if (static::$transactions == 1) {
            static::$transactions = 0;
            $this->driver->query("COMMIT");
        } else {
            static::$transactions -= 1;
        }
    }

    public function rollback()
    {
        if (static::$transactions == 1) {
            static::$transactions = 0;
            $this->driver->query('ROLLBACK');
        } else {
            static::$transactions -= 1;
        }
    }
}
