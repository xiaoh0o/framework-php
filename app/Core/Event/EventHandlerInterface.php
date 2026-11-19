<?php
namespace App\Core\Event;

/**
 * 事件处理程序接口
 */
interface EventHandlerInterface
{
    public function handle(mixed $data): void;
}
