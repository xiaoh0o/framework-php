<?php
namespace App\Core\Log;

/**
 * 日志处理程序
 */
class WriteToFileHandler implements LogHandlerInterface
{
    protected string $basePath = LOG_PATH;

    /**
     * 处理消息
     *
     * @param LogModel $logModel
     * @return void
     */
    public function handle(LogModel $logModel): void
    {
        $filename = $logModel->filename;
        $prefix   = $logModel->prefix;
        $ext = substr($filename, -4, 4);
        if ($ext == ".log" || $ext == ".txt") {
            $filename = substr($filename, 0, -4);
        }

        if ($filename == "") {
            $file = date('Ymd') .".log";
            if ($prefix != "") {
                $file = $prefix ."-". $file;
            }
        } else {
            $file = $filename .".log";
        }
        $filename = "{$this->basePath}/{$logModel->channel}/{$file}";
        $dirname = dirname($filename);

        $firstWrite = false;
        if (!file_exists($dirname)) {
            mkdir($dirname, 0777, true);
            $firstWrite = true;
        }

        $message  = $logModel->message;
        $context  = $logModel->context;

        $message = str_replace(["\r\n", "\r", "\n"], " ", $message);
        $context = empty($context) ? "" : json_encode($context, JSON_UNESCAPED_UNICODE);
        $content = trim("{$logModel->datetime} [{$logModel->level}] {$message} {$context}");

        // 写入文件
        file_put_contents($filename, $content.PHP_EOL, FILE_APPEND);

        /**
         * 如果是第一次写入，修改写入权限
         * 原因：PHP-CLI 模式创建的文件 默认所有者是 root, [apache,ngnix]没有写入权限
         */
        if ($firstWrite) {
            chmod($filename, 0777);
        }
    }
}
