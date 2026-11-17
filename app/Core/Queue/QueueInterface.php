<?php
namespace App\Core\Queue;

/**
 * 消息队列接口
 */
interface QueueInterface
{
    // 从头部获取
    public function pop();

    // 从尾部写入
    public function push(mixed $data);

    // 从头部写入 -- 优先处理
    public function rpush(mixed $data);
}
