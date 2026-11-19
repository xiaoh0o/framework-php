<?php
namespace App\Core\Http;

use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

/**
 * 响应类
 */
class Response extends SymfonyResponse
{
    /**
     * 发射数据
     *
     * @param string $body
     * @return void
     */
    public function emit($body)
    {
        $this->setContent($body);
        $this->send();
    }

    public function setHeaders(array $headers)
    {
        foreach ($headers as $name => $value) {
            $this->setHeader($name, $value);
        }
    }

    public function setHeader(string $name, mixed $value)
    {
        $this->headers->set($name, $value);
    }
}
