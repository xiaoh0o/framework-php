<?php
namespace App\Core\Database;

/**
 * 数据库事务接口
 */
interface TransactionInterface
{
    public function begin();

    public function commit();

    public function rollback();
}
