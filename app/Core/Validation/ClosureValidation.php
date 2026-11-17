<?php
namespace App\Core\Validation;

use Closure;

/**
 * 闭包 验证
 */
class ClosureValidation extends Validation
{
    public static function is(mixed $value, Closure $rules): bool
    {
        return $rules($value);
    }
}
