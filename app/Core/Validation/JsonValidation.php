<?php
namespace App\Core\Validation;

/**
 * Json 数据验证
 */
class JsonValidation extends Validation
{
    public static function is(mixed $value, array $rules)
    {
        if (is_string($value)) {
            $result = \json_decode($value);
            if (is_null($result)) {
                return false;
            }
        } else {
            return false;
        }

        foreach ($rules as $key => $val) {
            switch ($key) {
                case "required":
                    if (empty($result)) {
                        return false;
                    }
                    break;
            }
        }

        return true;
    }
}
