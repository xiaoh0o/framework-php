<?php
namespace App\Core\Event;

/**
 * 事件接口
 */
interface EventInterface
{
    // 发布队列消息
    public function publish(string $name, mixed $data, string $position = "tail"): void;

    // 返回事件队列
    public function queue(): EventQueue;

    // 返回事件日志
    public function logger(): EventLogger;

    // 返回所有处理器
    public function handlers(): array;
}
