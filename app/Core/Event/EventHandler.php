<?php
namespace App\Core\Event;

/**
 * 事件处理程序 基类
 */
abstract class EventHandler implements EventHandlerInterface
{
    /**
     * 执行事件处理逻辑
     *
     * @param mixed $data [事件处理的数据]
     */
    abstract public function handle(mixed $data): void;
}
