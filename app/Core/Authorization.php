<?php
namespace App\Core;

/**
 * 认证
*/
class Authorization
{
    private string $client_id = "";
    private string $secret    = "";
    private string $format    = "json";
    private string $sign_type = "md5";
    private string $version   = "1.0";

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
                "format"    => $this->format,
                "sign_type" => $this->sign_type,
                "version"   => $this->version
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
        $configs = Configure::get("authorization");
        $secret = $configs[$client_id] ?? "";
        if (!isset($configs[$client_id]) || empty($secret)) {
            throw new HttpException("Authorization failed", HttpException::UNAUTHENTICATED);
        }

        return new self($client_id, $secret);
    }
}
