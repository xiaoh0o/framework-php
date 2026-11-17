<?php
namespace App\Core\Validation;

/**
 * 对象 验证
 */
class ObjectValidation extends Validation
{
    public static function is(mixed $value, array $rules): bool
    {
        // 循环判断
        foreach ($rules as $key => $val) {
            switch ($key) {
                case 'class':
                    if (is_object($value) && get_class($value) == $val) {
                    } else {
                        return false;
                    }
                    break;
                default:
            }
        }

        return true;
    }
}
