<?php
namespace App\Controller\Frontend;

use Throwable;
use App\Core\HttpException;
use App\Core\Http\Response;

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
        return $this->echoResult(true, $message, HttpException::OK, $data);
    }

    /**
     * JSON 错误输出
     */
    protected function failed(Throwable $e)
    {
        return $this->echoResult(false, $e->getMessage(), $e->getCode());
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
    protected function echoResult(bool $status, string $message = "", int $code = HttpException::OK, array $data = [])
    {
        $result = json_encode([
            "error"   => $status ? 0 : -1,
            "code"    => $code,
            "message" => $message,
            "data"    => $data,
        ], JSON_UNESCAPED_UNICODE);

        $headers = [
            "Content-type" => "application/json; charset=utf-8"
        ];

        return new Response($result, Response::HTTP_OK, $headers);
    }
}
