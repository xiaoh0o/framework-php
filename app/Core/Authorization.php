<?php
namespace App\Core;

/**
 * 签名认证
*/
class Authorization
{
    private string $client_id = "";
    private string $secret    = "";
    private string $sign_type = "md5";

    public function __construct(string $client_id, string $secret)
    {
        $this->client_id = $client_id;
        $this->secret    = $secret;
    }

    // 验证签名
    public function validate(array $args, string $sign)
    {
        if ($this->sign($args) == $sign) {
            return true;
        }
        return false;
    }

    // 签名
    public function sign(array $args)
    {
        $args = $this->filter($args);
        ksort($args);

        $content = $this->secret;
        foreach ($args as $k => $v) {
            if (is_array($v)) {
                ksort($v);
                $v = json_encode($v);
            }
            if ("@" != substr($v, 0, 1)) {
                $content .= "$k$v";
            }
        }
        $content .= $this->secret;
        return strtoupper(md5($content));
    }

    // 创建请求数据
    public function create(array $args = [], bool $hasDefaultValue = true)
    {
        $data = [
            "client_id" => $this->client_id,
        ];
        if ($hasDefaultValue) {
            $data = [
                "client_id" => $this->client_id,
                "datetime"  => date("Y-m-d H:i:s"),
                "sign_type" => $this->sign_type
            ];
        }

        foreach ($args as $key => $value) {
            $data[$key] = $value;
        }
        $data['sign'] = $this->sign($data);
        return $data;
    }

    // 过滤掉空值
    private function filter(array $args)
    {
        $result = array();
        foreach ($args as $key => $val) {
            if ($key == "sign" || $key == "sign_type" || $val === "") {
                continue;
            } else {
                $result[$key] = $val;
            }
        }
        return $result;
    }

    public static function instance(string $client_id = "")
    {
        $config = Configure::get("authorization");

        if ($client_id == "") {
            $client_id = $config['keys']['default'] ?? "";
        }

        if (!isset($config["values"][$client_id])) {
            throw new HttpException("Not found authorization [client_id]", HttpException::UNAUTHENTICATED);
        }

        $secret = $config["values"][$client_id] ?? "";
        if (empty($secret)) {
            throw new HttpException("Not found authorization [client_id]", HttpException::UNAUTHENTICATED);
        }

        return new self($client_id, $secret);
    }
}
