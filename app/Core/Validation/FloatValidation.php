<?php
namespace App\Core\Validation;

use App\Core\Utils\Math;

/**
 * 浮点数 验证
 */
class FloatValidation extends Validation
{
    public static function is(mixed $value, array $rules): bool
    {
        // 类型判断
        if (Math::isInt($value) || Math::isFloat($value)) {
            // 整型，或浮点型都可以
        } else {
            return false;
        }

        $value = floatval($value);

        // 循环判断
        foreach ($rules as $key => $val) {
            switch ($key) {
                case 'pattern':
                    if (! preg_match($val, $value)) {
                        return false;
                    }
                    break;
                case "min": // 最小值
                    if ($value < floatval($val)) {
                        return false;
                    }
                    break;
                case "max": // 最大值
                    if ($value > floatval($val)) {
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
