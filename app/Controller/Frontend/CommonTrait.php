<?php
namespace App\Controller\Frontend;

use Nette\Http\Response;
use Throwable;
use Xiaoh0o\Utils\HttpException;

trait CommonTrait
{
    /**
     * JSON 成功的输出
     *
     * @param array $data
     * @return string
     */
    protected function success(array $data = [], string $message = "ok")
    {
        return $this->json(true, $message, HttpException::OK, $data);
    }

    /**
     * JSON 错误输出
     */
    protected function failed(Throwable $e)
    {
        return $this->json(false, $e->getMessage(), $e->getCode());
    }

    /**
     * 输出 JSON
     *
     * @param boolean $status
     * @param string $message
     * @param int $code HTTP_CODE 错误码
     * @param array $data 相关数据
     * @return string
     */
    protected function json(bool $status, string $message = "", int $code = HttpException::OK, array $data = [])
    {
        $result = [
            "error"   => $status ? 0 : -1,
            "code"    => $code,
            "message" => $message,
            "data"    => $data,
        ];
        $httpResponse = new Response();
        $httpResponse->setContentType("application/json", "utf-8");

        // 重新设置返回值 Header
        return json_encode($result, JSON_UNESCAPED_UNICODE);
    }
}
