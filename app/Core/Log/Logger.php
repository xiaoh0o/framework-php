<?php
namespace App\Core\Log;

/**
 * 日志
 */
class Logger implements LoggerInterface
{
    protected string $channel  = "";
    protected string $filename = "";
    protected array  $extra    = [];
    protected string $prefix   = "";

    protected ?LogHandlerInterface $driver = null;

    public function __construct(string $channel, string $filename = "")
    {
        $this->channel  = trim($channel);
        $this->filename = trim($filename);
    }

    public function setPrefix(string $prefix)
    {
        $this->prefix = trim($prefix);
    }

    public function setDriver(LogHandlerInterface $driver)
    {
        $this->driver = $driver;
    }

    // 消息写入队列
    public function log(string $level, string $message, array $context = []): void
    {
        $logModel = LogModel::build([
            "channel"  => $this->channel,
            "filename" => $this->filename,
            "prefix"   => $this->prefix,
            "level"    => $level,
            "message"  => $message,
            "context"  => $context,
            "datetime" => date("Y-m-d H:i:s O"), // 时间带 GMT 差值 (+0800)
        ]);
        if (is_null($this->driver)) {
            $this->driver = new WriteToFileHandler();
        }
        $this->driver->handle($logModel);
    }

    // 详细的信息
    public function debug(string $message, array $context = []): void
    {
        $this->log(self::DEBUG, $message, $context);
    }

    // 关键事件
    public function info(string $message, array $context = []): void
    {
        $this->log(self::INFO, $message, $context);
    }

    // 普通但是重要的事件
    public function notice(string $message, array $context = []): void
    {
        $this->log(self::NOTICE, $message, $context);
    }

    // 出现非错误的异常
    public function warning(string $message, array $context = []): void
    {
        $this->log(self::WARNING, $message, $context);
    }

    // 运行时错误，但是不需要立刻处理
    public function error(string $message, array $context = []): void
    {
        $this->log(self::ERROR, $message, $context);
    }

    // 严重错误
    public function critical(string $message, array $context = []): void
    {
        $this->log(self::CRITICAL, $message, $context);
    }

    // 系统不可用
    public function emergency(string $message, array $context = []): void
    {
        $this->log(self::EMERGENCY, $message, $context);
    }
}
