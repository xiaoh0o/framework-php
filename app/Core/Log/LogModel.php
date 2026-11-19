<?php
namespace App\Core\Log;

use App\Core\ValueObject;

/**
 * 日志消息体模型
 */
class LogModel extends ValueObject
{
    public function __construct(
        protected string $channel,  // 日志文件夹下 创建的文件夹
        protected string $filename, // 日志文件名
        protected string $prefix,   // 文件名称的前缀
        protected string $level,    // 级别
        protected string $datetime, // 创建日期
        protected string $message,  // 消息内容
        protected array  $context   // 上下文信息
    ) {
    }
}
