<?php
namespace App\Core\Database;

use Closure;

/**
 * 数据库接口
 */
interface DatabaseInterface
{
    // 查询一条
    public function row(string $sql): ?array;

    // 获取一行或一个字段
    public function get(string $sql): int|string|null;

    // 查询多条
    public function rows(string $sql): array;

    // 请求一条
    public function query(string $sql);

    // 写入
    public function insert(string $table, array $data): int;

    // 更新
    public function update(string $table, array $data, string|array $condition): int;

    // 删除
    public function delete(string $table, string|array $condition, int $limit = 0): int;

    // 受影响的行数
    public function affectedRows(): int;

    // 新建ID
    public function id(): int;

    // 获取请求队列
    public function sqls(): array;

    // 获取连接的数据库名称
    public function dbname(): string;

    // 自增
    public function incr(string $table, array $incrData, array $updateData, string|array $condition): int;

    // 自减
    public function decr(string $table, array $decrData, array $updateData, string|array $condition): int;

    // 转义字符串
    public function encode(string $content): string;

    // 转换条件语句
    public function where(string|array $condition): string;

    // 返回事务类
    public function transaction(): TransactionInterface;

    // 封装好的事务
    public function doTransaction(Closure $callback);
}
