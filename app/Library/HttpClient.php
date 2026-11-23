<?php
namespace App\Library;

use Exception;

/**
 * HTTP 请求
*/
class HttpClient
{
    protected string $gateway = "";
    protected array $headers = [];
    protected int $timeout = 120;

    protected string $effective_url = "";
    protected int $http_code     = 0;

    public function setTimeout(int $timeout)
    {
        if ($timeout > 0) {
            $this->timeout = $timeout;
        }
    }

    // 设置请求 Headers
    public function setHeaders(array $headers)
    {
        foreach ($headers as $key => $values) {
            $this->setHeader($key, $values);
        }
    }

    // 设置 Header
    public function setHeader(string $key, string|array $values, bool $replace = true)
    {
        $key = str_replace('_', '-', strtolower($key));
        if (\is_array($values)) {
            $values = array_values($values);

            if (true === $replace || !isset($this->headers[$key])) {
                $this->headers[$key] = $values;
            } else {
                $this->headers[$key] = array_merge($this->headers[$key], $values);
            }
        } else {
            if (true === $replace || !isset($this->headers[$key])) {
                $this->headers[$key] = [$values];
            } else {
                $this->headers[$key][] = $values;
            }
        }
    }

    // 获取 所有 Header
    public function getHeaders()
    {
        $headers = [];
        ksort($this->headers);
        foreach ($this->headers as $key => $values) {
            $key = ucwords($key, "-");
            $value = implode(",", $values);
            $headers[] = "{$key}:{$value}";
        }
        return $headers;
    }

    /**
     * @param string $url 地址
     * @param string $method 请求方式
     * @param array $parameters 请求参数
     * @param array $files 本地文件地址数组
    */
    public function request(
        string $url,
        string $method = 'GET',
        array $parameters = [],
        array $files = []
    ) {
        $method = strtoupper($method);
        if (!in_array($method, ['POST', 'GET', 'DELETE', 'PATCH', 'PUT', 'HEAD'])) {
            throw new Exception("Bad request method！[curl]");
        }

        // 完整的请求地址
        if ($this->gateway != "") {
            $url = ltrim($url, "/");
            if ($url == "") {
                $url = $this->gateway;
            } else {
                $url = $this->gateway . "/{$url}";
            }
        }
        $url = rtrim($url, "?");

        $options = [
            CURLOPT_URL            => $url,
            CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_ENCODING       => "gzip",
            CURLOPT_CONNECTTIMEOUT => 5,
            CURLOPT_TIMEOUT        => $this->timeout,
            CURLINFO_HEADER_OUT    => true,
        ];

        switch ($method) {
            case 'HEAD': // 请求头
                $options[CURLOPT_CUSTOMREQUEST] = 'HEAD';
                $options[CURLOPT_NOBODY] = true;
                break;
            case 'POST': // 创建
                $options[CURLOPT_POST] = true;
                $options[CURLOPT_POSTFIELDS] = $this->buildRequestBody($parameters, $files);
                break;
            case 'GET': // 请求
                if (!empty($parameters)) {
                    $url = $url . "?" . http_build_query($parameters);
                }
                break;
            case 'DELETE': // 删除
            case 'PATCH': // 更新
            case 'PUT': // 全量更新
                $options[CURLOPT_CUSTOMREQUEST] = $method;
                $options[CURLOPT_POSTFIELDS] = $this->buildRequestBody($parameters, $files);
                break;
        }

        $options[CURLOPT_HTTPHEADER] = $this->getHeaders();

        $curl = curl_init();
        curl_setopt_array($curl, $options);
        $response = curl_exec($curl);

        // 实际访问的地址，301 302
        $this->effective_url = curl_getinfo($curl, CURLINFO_EFFECTIVE_URL);
        $this->http_code     = curl_getinfo($curl, CURLINFO_HTTP_CODE);

        // 判断是否请求成功
        $errno = curl_errno($curl);
        $error = curl_error($curl);

        if ($errno == 0) {
            return $response;
        } else {
            throw new Exception("Bad request! [curl]". $error);
        }
    }

    // 返回实际访问的地址
    public function getEffectiveUrl()
    {
        return $this->effective_url;
    }

    // 返回请求http_code编码
    public function getHttpCode()
    {
        return $this->http_code;
    }

    /**
     * 创建POST 请求
     *
     * @param array $parameters
     * @param array $files
     * @return array
     */
    private function buildRequestBody(array $parameters, array $files = [])
    {
        // [content-type: multipart/form-data]
        if (count($files) > 0) {
            $this->setHeader("content-type", "multipart/form-data");
            $this->setHeader("Except", "");
            foreach ($files as $key => $file) {
                $file = realpath($file);
                if ($file) {
                    $finfo = $this->fileinfo($file);
                    if ($finfo) {
                        $parameters[$key] = new \CURLFile($file, $finfo['mime'], $finfo['name']);
                    }
                } else {
                    throw new Exception("Invalid file! [curl]");
                }
            }
            return $parameters;
        }

        $content_type = $this->headers["content-type"][0] ?? "";

        // [content-type: application/json] [payload]
        if (stripos($content_type, "application/json") !== false) {
            return json_encode($parameters, JSON_UNESCAPED_UNICODE);
        }

        // [content-type: application/x-www-form-urlencoded]
        return http_build_query($parameters);
    }

    // 获取文件信息
    private function fileinfo(string $file)
    {
        if (file_exists($file)) {
            return [
                "path" => $file,
                "mime" => \mime_content_type($file),
                "name" => \basename($file)
            ];
        }
        return false;
    }

    // 设置网关地址
    public function gateway(string $gateway)
    {
        $this->gateway = trim($gateway, "/");
    }

    // GET 请求
    public function get(string $url, array $parameters = [])
    {
        return $this->request($url, "GET", $parameters);
    }

    public function query(string $url, array $parameters = [])
    {
        return $this->get($url, $parameters);
    }

    // POST 请求
    public function post(string $url, array $parameters = [], array $files = [])
    {
        return $this->request($url, "POST", $parameters, $files);
    }

    // 上传 请求
    public function upload(string $url, array $files, array $parameters = [])
    {
        return $this->request($url, "POST", $parameters, $files);
    }

    // PUT 全量更新
    public function put(string $url, array $parameters = [], array $files = [])
    {
        return $this->request($url, "PUT", $parameters, $files);
    }

    // PATCH 部分更新
    public function patch(string $url, array $parameters = [], array $files = [])
    {
        return $this->request($url, "PATCH", $parameters, $files);
    }

    // 请求头
    public function head(string $url, array $parameters = [])
    {
        return $this->request($url, "HEAD", $parameters);
    }

    // DELETE 删除
    public function delete(string $url, array $parameters = [])
    {
        return $this->request($url, "DELETE", $parameters);
    }
}
