<?php
namespace App\Core\Log;

interface LoggerInterface
{
    const DEBUG     = 'DEBUG';
    const INFO      = 'INFO';
    const NOTICE    = 'NOTICE';
    const WARNING   = 'WARNING';
    const ERROR     = 'ERROR';
    const CRITICAL  = 'CRITICAL';
    const ALERT     = 'ALERT';
    const EMERGENCY = 'EMERGENCY';

    public function log(string $level, string $message, array $context): void;

    // 详细的信息
    public function debug(string $message, array $context): void;

    // 关键事件
    public function info(string $message, array $context): void;

    // 普通但是重要的事件
    public function notice(string $message, array $context): void;

    // 出现非错误的异常
    public function warning(string $message, array $context): void;

    // 运行时错误，但是不需要立刻处理
    public function error(string $message, array $context): void;

    // 严重错误
    public function critical(string $message, array $context): void;

    // 系统不可用
    public function emergency(string $message, array $context): void;
}
