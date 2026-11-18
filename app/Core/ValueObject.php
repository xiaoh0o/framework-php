<?php
namespace App\Core;

use Exception;
use ReflectionClass;

/**
 * 值对象模型，初始化后不可修改
 */
abstract class ValueObject extends ObjectArrayAccess
{
    private static array $infoCache = [];

    // 数组方式返回
    final public function toArray(): array
    {
        $names = static::getPropertyNames();
        if (empty($names)) {
            return [];
        }

        $data = [];
        foreach ($names as $name) {
            $data[$name] = $this->__get($name);
        }
        return $data;
    }

    // 格式化返回
    public function format(): array
    {
        return $this->toArray();
    }

    /**
     * 用新数据替换 获取新的对象
     *
     * @param array $data 属性键值对
     * @return static
     */
    public function with(array $data): static
    {
        $properties = $this->toArray();
        if (!empty($data)) {
            // 使用新属性值替换
            foreach ($data as $key => $value) {
                if (array_key_exists($key, $properties)) {
                    $properties[$key] = $value;
                } else {
                    $class = get_called_class();
                    throw new Exception("Property is not exist! {$class}::{$key}");
                }
            }
        }

        // 返回一个新对象
        return static::build($properties);
    }

    // 获取模型属性名
    protected static function getPropertyNames()
    {
        return array_keys(static::getPropertyInfos());
    }

    // 获取属性信息 [name => type|class]
    protected static function getPropertyInfos()
    {
        $class = get_called_class();
        if (isset(self::$infoCache[$class])) {
            return self::$infoCache[$class];
        }

        $reflectionClass = new ReflectionClass($class);

        // 构造函数
        $constructor = $reflectionClass->getConstructor();
        if (is_null($constructor)) {
            return self::$infoCache[$class] = [];
        }

        // 获取参数
        $parameters = $constructor->getParameters();
        if (empty($parameters)) {
            return self::$infoCache[$class] = [];
        }

        // 获取属性名称和类型
        $result = [];
        foreach($parameters as $parameter) {
            // 不支持可变参数
            if ($parameter->isVariadic()) {
                throw new Exception("构造函数不支持使用可变参数！");
            }

            $name = $parameter->getName();

            // 默认值
            $hasDefaultValue = $parameter->isDefaultValueAvailable();
            $defaultValue = null;
            if ($hasDefaultValue) {
                $defaultValue = $parameter->getDefaultValue();
            }

            // 没有类型
            if ($parameter->hasType() == false) {
                $result[$name] = [
                    "types"           => ["mixed"],
                    "hasDefaultValue" => $hasDefaultValue,
                    "defaultValue"    => $defaultValue
                ];
                continue;
            }

            // 存在类型
            $reflectionType = $parameter->getType();
            $typeNames = [];

            // 命名类型
            if ($reflectionType instanceof \ReflectionNamedType) {
                $typeNames[] = $reflectionType->getName();
                if ($reflectionType->allowsNull()) {
                    $typeNames[] = null;
                }
            }
            // 联合类型
            elseif ($reflectionType instanceof \ReflectionUnionType) {
                foreach ($reflectionType->getTypes() as $typeItem) {
                    if ($typeItem->getName() === 'null') {
                        $typeNames[] = null; // 统一用 PHP null 表示"可为空"
                    } else {
                        $typeNames[] = $typeItem->getName();
                    }
                }
            }
            // 交集类型：只准许 指定类名或接口名
            elseif ($reflectionType instanceof \ReflectionIntersectionType) {
                $typeNames[] = "intersection";
            }
            // null 类型
            else {
                $typeNames[] = null;
            }
            $result[$name] = [
                "hasDefaultValue" => $hasDefaultValue,
                "defaultValue"    => $defaultValue,
                "types"           => $typeNames
            ];
        }

        return self::$infoCache[$class] = $result;
    }

    // 通过数组创建模型
    public static function build(array $data): static
    {
        $class = get_called_class();
        $infos = static::getPropertyInfos();
        if (empty($infos)) {
            return new $class();
        }

        $args = [];
        foreach ($infos as $name => $typeInfo) {
            // 有值
            if (array_key_exists($name, $data)) {
                $value = $data[$name];
            }
            // 没有值
            else {
                // 有默认值
                if ($typeInfo['hasDefaultValue']) {
                    $value = $typeInfo['defaultValue'];
                }
                // 没有默认值 -- 判断类型
                else {
                    // 判断是否可以为null
                    if (in_array(null, $typeInfo['types'], true)) {
                        $value = null;
                    }
                    elseif (in_array('int', $typeInfo['types'], true)) {
                        $value = 0;
                    }
                    elseif (in_array('float', $typeInfo['types'], true)) {
                        $value = 0.0;
                    }
                    elseif (in_array("string", $typeInfo['types'], true)) {
                        $value = "";
                    }
                    elseif (in_array("array", $typeInfo['types'], true)) {
                        $value = [];
                    }
                    elseif (in_array("intersection", $typeInfo['types'], true)) {
                        throw new Exception("该属性是交集类型，必须显式传值! {$class}::{$name}");
                    }
                    else {
                        throw new Exception("该属性值不存在，必须显式传值! {$class}::{$name}");
                    }
                }
            }

            $args[] = $value;
        }

        return new $class(...$args);
    }
}
