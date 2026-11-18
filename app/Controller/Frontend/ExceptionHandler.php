<?php
namespace App\Controller\Frontend;

use Throwable;

/**
 * 异常输出处理
 */
class ExceptionHandler
{
    use CommonTrait;

    public function render(Throwable $e)
    {
        $message = $e->getMessage();

        return $this->echoResult(false, $message, $e->getCode(), []);
    }
}
