<?php
namespace App\Core;

/**
 * 异常基类
 */
class HttpException extends \Exception
{
    const OK = 200;
    const INVALID_ARGUMENT = 400; // 无效的请求参数
    const FAILED_PRECONDITION = 400; // 当前的系统状态不能执行请求
    const OUT_OF_RANGE = 400; // 客户端指定无效范围
    const UNAUTHENTICATED = 401; // 请求未通过身份验证
    const PERMISSION_DENIED = 403; // 客户端没有足够的权限
    const NOT_FOUND = 404; // 不存在
    const METHOD_NOT_ALLOWED = 405; // 请求不准许
    const ABORTED = 409; // 并发冲突
    const ALREADY_EXISTS = 409; // 客户端试图创建的资源已经存在
    const RESOURCE_EXHAUSTED = 429; // 超过资源限额或频率限制
    const CANCELLED = 499; // 客户端取消请求
    const DATA_LOSS = 500; // 不可恢复的数据丢失或损坏
    const UNKNOWN = 500; // 服务端未知错误，BUG
    const INTERNAL = 500; // 服务端内部错误，BUG
    const NOT_IMPLEMENTED = 501; // 未实现的接口
    const UNAVAILABLE = 503; // 服务不可用
    const DEADLINE_EXCEEDED = 504; // 请求超过最后期限，如果重复发生，请考虑减少请求的复杂性

    protected array $data;

    public function __construct(string $message, int $code = self::INTERNAL, array $data = [])
    {
        $this->data = $data;
        parent::__construct($message, $code);
    }

    // 获取异常数据
    public function getData()
    {
        return $this->data;
    }
}
