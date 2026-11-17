<?php
namespace App\Core\Validation;

/**
 * 字符串 验证
 */
class ArrayValidation extends Validation
{
    public static function is(mixed $value, array $rules): bool
    {
        // 判断类型
        if (! is_array($value)) {
            return false;
        }

        // 循环判断
        foreach ($rules as $key => $val) {
            switch ($key) {
                case "required": // 不能为空
                    if (empty($value)) {
                        return false;
                    }
                    break;
                default:
            }
        }

        return true;
    }
}
