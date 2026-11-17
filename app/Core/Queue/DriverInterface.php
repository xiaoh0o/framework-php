<?php
namespace App\Core\Queue;

/**
 * 队列驱动接口
 */
interface DriverInterface
{
    // 设置队列名称
    public function setChannel(string $channel);

    // 头部取出
    public function pop();

    // 尾部写入
    public function push(mixed $data);

    // 头部写入
    public function rpush(mixed $data);

    // 队列长度
    public function length(): int;
}
