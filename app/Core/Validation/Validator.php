<?php
namespace App\Core\Validation;

use Exception;
use App\Core\Validation\Validation;
use App\Core\Validation\IntValidation;
use App\Core\Validation\FloatValidation;
use App\Core\Validation\NumberValidation;
use App\Core\Validation\StringValidation;
use App\Core\Validation\JsonValidation;
use App\Core\Validation\ArrayValidation;
use App\Core\Validation\ObjectValidation;
use App\Core\Validation\ClosureValidation;
use App\Core\Validation\EnumValidation;
use App\Core\Validation\BooleanValidation;
use App\Core\Validation\BasicValidation;

/**
 * 验证器
 */
class Validator extends Validation
{
    // 单条验证
    public static function is(mixed $value, string $rule): bool
    {
        // 根据规则，判断出数据类型
        $result = self::parseRules($rule);
        $type  = $result['type'];
        $rules = $result['rules'];
        switch ($type) {
            case self::TYPE_BOOLEAN:
                return BooleanValidation::is($value);
            case self::TYPE_INT:
                return IntValidation::is($value, $rules);
            case self::TYPE_FLOAT:
                return FloatValidation::is($value, $rules);
            case self::TYPE_NUMBER:
                return NumberValidation::is($value, $rules);
            case self::TYPE_CLOSURE:
                return ClosureValidation::is($value, $rules);
            case self::TYPE_STRING:
                return StringValidation::is($value, $rules);
            case self::TYPE_ARRAY:
                return ArrayValidation::is($value, $rules);
            case self::TYPE_JSON:
                return JsonValidation::is($value, $rules);
            case self::TYPE_ENUM:
                return EnumValidation::is($value, $rules);
            case self::TYPE_OBJECT:
                return ObjectValidation::is($value, $rules);
            case self::TYPE_BASIC:
                return BasicValidation::is($value, $rules);
        }
        throw new Exception("数据验证，数据类型不存在！");
    }
}
