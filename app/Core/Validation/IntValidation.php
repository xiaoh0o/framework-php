<?php
namespace App\Core\Validation;

use App\Core\Utils\Math;

/**
 * 整型 验证
 */
class IntValidation extends Validation
{
    public static function is(mixed $value, array $rules): bool
    {
        // 类型判断
        if (! Math::isInt($value)) {
            return false;
        }

        $value = intval($value);

        // 循环判断
        foreach ($rules as $key => $val) {
            switch ($key) {
                case 'pattern':
                    if (! preg_match($val, $value)) {
                        return false;
                    }
                    break;
                case "min": // 最小值
                    if ($value < intval($val)) {
                        return false;
                    }
                    break;
                case "max": // 最大值
                    if ($value > intval($val)) {
                        return false;
                    }
                    break;
                case "required": // 不能为零
                    if ($value == 0) {
                        return false;
                    }
                    break;
                default:
            }
        }

        return true;
    }
}
