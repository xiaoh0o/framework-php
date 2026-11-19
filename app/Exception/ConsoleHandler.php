<?php
namespace App\Exception;

use Throwable;

/**
 * 控制台
 */
class ConsoleHandler
{
    public function render(Throwable $e)
    {
        return json_encode([
            "code"    => $e->getCode(),
            "time"    => date('Y-m-d H:i:s'),
            "message" => $e->getMessage(),
            "file"    => $e->getFile(),
            "line"    => $e->getLine(),
            "trace"   => $e->getTrace(),
            "data"    => [],
        ], JSON_UNESCAPED_UNICODE).PHP_EOL.PHP_EOL;
    }
}
