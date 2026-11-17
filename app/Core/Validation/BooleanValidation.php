<?php
namespace App\Core\Validation;

/**
 * 布尔值验证
 */
class BooleanValidation extends Validation
{
    public static function is(mixed $value): bool
    {
        if (is_bool($value)) {
            return true;
        }
        return false;
    }
}
