<?php
namespace App\Exception;

use Throwable;

/**
 * 异常输出处理
 */
class DefaultHandler
{
    public function render(Throwable $e)
    {
        return json_encode([
            "error"   => -1,
            "code"    => $e->getCode(),
            "message" => $e->getMessage(),
            "time"    => date("Y-m-d H:i:s"),
            "data"    => [],
        ]);
    }
}
