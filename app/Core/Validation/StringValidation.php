<?php
namespace App\Core\Validation;

/**
 * 字符串 验证
 */
class StringValidation extends Validation
{
    public static function is(mixed $value, array $rules): bool
    {
        // 循环判断
        foreach ($rules as $key => $val) {
            switch ($key) {
                case 'pattern':
                    if (! preg_match($val, $value)) {
                        return false;
                    }
                    break;
                case "min": // 最小值
                    if (mb_strlen($value, 'UTF-8') < intval($val)) {
                        return false;
                    }
                    break;
                case "max": // 最大值
                    if (mb_strlen($value, 'UTF-8') > intval($val)) {
                        return false;
                    }
                    break;
                case "required": // 不能为零
                    if (trim($value) == "") {
                        return false;
                    }
                    break;
                default:
            }
        }

        return true;
    }
}
