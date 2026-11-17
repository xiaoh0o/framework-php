<?php
namespace App\Core\Validation;

use Exception;
use Closure;

/**
 * 验证通用方法
 */
class Validation
{
    const TYPE_BOOLEAN = "bool";
    const TYPE_INT     = "int";
    const TYPE_FLOAT   = "float";
    const TYPE_NUMBER  = "number";
    const TYPE_STRING  = "string";
    const TYPE_CLOSURE = "closure";
    const TYPE_JSON    = "json";
    const TYPE_ARRAY   = "array";
    const TYPE_ENUM    = "enum";
    const TYPE_OBJECT  = "class";
    const TYPE_BASIC   = "basic"; // 6种基本类型

    // 判断是否为类型
    protected static function isType(string $value)
    {
        return in_array($value, [
            self::TYPE_BOOLEAN,
            self::TYPE_INT,
            self::TYPE_FLOAT,
            self::TYPE_JSON,
            self::TYPE_NUMBER,
            self::TYPE_STRING,
            self::TYPE_ARRAY,
            self::TYPE_ENUM,
            self::TYPE_OBJECT,
            self::TYPE_BASIC,
        ]);
    }

    // 解析出所有规则
    // 规则格式：type:int|min:0|max:99|required
    protected static function parseRules(Closure|string $rule)
    {
        // 闭包类型，{$rule} 就是闭包方法
        if ($rule instanceof Closure) {
            return [
                "type"  => self::TYPE_CLOSURE,
                "rules" => $rule
            ];
        }

        $result = [
            "type"  => self::TYPE_STRING,
            "rules" => []
        ];

        $rules = explode("|", $rule);
        foreach ($rules as $theRule) {
            $array = explode(':', $theRule);
            $count = count($array);
            $ruleValue = $array[0];
            if ($count == 1) {
                if (self::isType($ruleValue)) {
                    $result["type"] = $ruleValue;
                } else {
                    $result['rules'][$ruleValue] = $ruleValue;
                }
            } elseif ($count == 2) {
                // 明确写明 type:{$type}
                if ($ruleValue == "type") {
                    if (self::isType($ruleValue)) {
                        $result["type"] = $ruleValue;
                    } else {
                        throw new Exception("验证规则，语法错误！");
                    }
                } else {
                    if ($ruleValue == self::TYPE_ENUM) {
                        $result["rules"][$ruleValue] = explode(',', $array[1]);
                    } else {
                        $result["rules"][$ruleValue] = $array[1];
                    }
                }
            }
        }

        return $result;
    }
}
