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
        $message  = $e->getMessage() ."<br />";
        $message .= $e->getFile() ." Line:". $e->getLine();

        return $this->json(false, $message, $e->getCode(), []);
    }
}
