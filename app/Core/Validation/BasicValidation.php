<?php
namespace App\Core\Validation;

use App\Core\Utils\Math;

/**
 * 基本类型
 */
class BasicValidation extends Validation
{
    // 类型支持 number[int, long, float], bool, array, string, null
    public static function is(mixed $value, array $rules): bool
    {
        if (!Math::isNumber($value) &&
            !is_string($value) &&
            !is_array($value) &&
            !is_null($value) &&
            !is_bool($value)
        ) {
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
