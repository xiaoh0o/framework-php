<?php
namespace App\Core\Validation;

/**
 * 枚举 验证
 */
class EnumValidation extends Validation
{
    public static function is(mixed $value, array $rules): bool
    {
        // 循环判断
        foreach ($rules as $key => $val) {
            switch ($key) {
                case 'enum':
                    if (! in_array($value, $val)) {
                        return false;
                    }
                    break;
                default:
            }
        }

        return true;
    }
}
