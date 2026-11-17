<?php
namespace App\Core\Utils;

use Exception;

/**
 * 格式化数据
 */
class Formatter
{
    // 格式化为整型
    public static function toInt(mixed $value)
    {
        return Math::toInt($value);
    }

    // 格式化为浮点型
    public static function toFloat(mixed $value)
    {
        return Math::toFloat($value);
    }

    // 格式化为字符串
    public static function toString(mixed $value, bool $trim = false)
    {
        if (is_string($value)) {
            return $trim ? self::trim($value) : $value;
        }
        if (is_numeric($value) || is_bool($value)) {
            $value = strval($value);
            return $trim ? self::trim($value) : $value;
        }
        if (is_null($value)) {
            return "";
        }

        throw new Exception("无效的数据类型！[string]");
    }

    // 清除开头结尾的空格
    public static function trim(string $value)
    {
        // 清除尾部空格
        while (true) {
            $value = rtrim($value);
            if (mb_substr($value, -1, 1) == chr(194).chr(160)) {
                $value = mb_substr($value, 0, -1);
            } else {
                break;
            }
        }

        // 清掉头部的空格
        while (true) {
            $value = ltrim($value);
            if (mb_substr($value, 0, 1) == chr(194).chr(160)) {
                $value = mb_substr($value, 1);
            } else {
                break;
            }
        }

        return $value;
    }

    // 转换成数组
    public static function toArray(array &$values, string $datatype = "")
    {
        if ($datatype == 'int') {
            foreach ($values as $index => $value) {
                $values[$index] = self::toInt($value);
            }
        } elseif ($datatype == 'float') {
            foreach ($values as $index => $value) {
                $values[$index] = self::toFloat($value);
            }
        } elseif ($datatype == "string") {
            foreach ($values as $index => $value) {
                $values[$index] = self::toString($value);
            }
        }

        return $values;
    }

    // 转换成 布尔型
    public static function toBool(mixed $value)
    {
        if ($value === true || $value === 1 || $value === "1" || $value === "true") {
            return true;
        }

        if ($value === false ||
            $value === "false" ||
            $value === 0 ||
            $value === "" ||
            $value === "0" ||
            is_null($value)
        ) {
            return false;
        }

        throw new Exception("无效的数据类型! [bool]");
    }

    /**
     * 批量格式化返回一个数组
     *
     * @param array $data // 待处理数组
     * @param array $datatype // 字段及类型 ["key" => "int", ...]
     * @return array
     */
    public static function batch(array $data, array $datatype): array
    {
        $result = [];
        foreach ($datatype as $key => $types) {
            $types = (array)$types;

            // 转换成整型
            if ($types[0] == "int") {
                $defaultValue = $types[1] ?? 0;
                if (!is_int($defaultValue)) {
                    throw new Exception("Default value is not int");
                }
                $result[$key] = static::toInt($data[$key] ?? $defaultValue);
                continue;
            }

            // 转换成字符串
            if ($types[0] == "string") {
                $defaultValue = $types[1] ?? "";
                if (!is_string($defaultValue)) {
                    throw new Exception("Default value is not string");
                }
                $result[$key] = static::toString($data[$key] ?? $defaultValue);
                continue;
            }

            // 转换成浮点型
            if ($types[0] == "float") {
                $defaultValue = $types[1] ?? 0;
                if (!is_float($defaultValue) && !is_int($defaultValue)) {
                    throw new Exception("Default value is not float");
                }
                $result[$key] = static::toFloat($data[$key] ?? $defaultValue);
                continue;
            }

            // 转换成布尔值
            if ($types[0] == "bool" || $types[0] == "boolean") {
                $defaultValue = $types[1] ?? null;
                if (!is_bool($defaultValue) && !is_null($defaultValue)) {
                    throw new Exception("Default value is not bool");
                }
                $result[$key] = static::toBool($data[$key] ?? $defaultValue);
                continue;
            }

            // 转换成数组
            if (stripos($types[0], "array") === 0) {
                $arrayValue = $data[$key] ?? [];
                $arrayValue = (array)$arrayValue;

                $itemType = "";
                if ($types[0] == "array:int") {
                    $itemType = "int";
                } elseif ($types[0] == "array:string") {
                    $itemType = "string";
                } elseif ($types[0] == "array:float") {
                    $itemType = "float";
                }

                $result[$key] = static::toArray($arrayValue, $itemType);
                continue;
            }
        }

        return $result;
    }

    /**
     * json 字符串转换成 数组
     *
     * @param array|string $value
     * @param boolean $force
     * @return array
     */
    public static function jsonToArray($value, bool $force = false): array
    {
        if (is_array($value)) {
            return $value;
        }
        if (is_string($value)) {
            $result = @json_decode($value, true);
            if (is_array($result)) {
                return $result;
            }
        }
        if ($force) {
            return (array)$value;
        }
        return [];
    }

    /**
     * 通过分隔符拆分成数组
     *
     * @param string|array $value
     * @param string $datatype  // string, int, float
     * @param array $separators // 分隔符
     * @return array
     */
    public static function splitToArray(string $value, string $datatype = "", array $separators = [",", "\n", "，", "、"]): array
    {
        if (empty($separators)) {
            throw new Exception("Invalid parameter!");
        }

        $value = trim($value);
        $value = preg_replace("/\x20{2,}/", " ", $value); // 连续的空格
        $value = preg_replace("/\s+\n/", "\n", $value);   // 连续的空格带换行
        $value = str_replace($separators, ",", $value);
        $array = explode(",", $value);

        $result = [];
        foreach ($array as $itemValue) {
            $itemValue = trim($itemValue);
            if ($itemValue == "") {
                continue;
            }
            switch ($datatype) {
                case "int":
                    $itemValue = static::toInt($itemValue);
                    break;
                case "float":
                    $itemValue = static::toFloat($itemValue);
                    break;
                case "string":
                    $itemValue = static::toString($itemValue);
                    break;
            }
            $result[] = $itemValue;
        }

        return $result;
    }
}
