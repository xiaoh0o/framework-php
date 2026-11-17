<?php
namespace App\Core;

use Exception;
use ReflectionClass;
use ReflectionNamedType;

/**
 * 值对象模型，初始化后不可修改
 */
abstract class ValueObject extends ObjectArrayAccess
{
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
    public function format()
    {
        return $this->toArray();
    }

    // 批量格式化返回
    public static function formats(array $models, bool $hasKey = true)
    {
        $data = [];
        foreach ($models as $key => $model) {
            $value = $model->format();
            if ($hasKey) {
                $data[$key] = $value;
            } else {
                $data[] = $value;
            }
        }
        return $data;
    }

    // 属性的验证规则
    protected static function rules()
    {
        return [];
    }

    /**
     * 用新数据替换 获取新的对象
     *
     * @param array $data 属性键值对
     * @return object
     */
    public function with(array $data)
    {
        $properties = $this->toArray();
        if (!empty($data)) {
            // 使用新属性值替换
            foreach ($data as $key => $value) {
                if (isset($properties[$key])) {
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
        $class = get_called_class();
        $reflectionClass = new ReflectionClass($class);

        // 构造函数
        $constructor = $reflectionClass->getConstructor();
        if (is_null($constructor)) {
            return [];
        }

        // 获取参数
        $parameters = $constructor->getParameters();
        if (empty($parameters)) {
            return [];
        }

        // 获取属性名称
        $propertyNames = [];
        foreach($parameters AS $parameter) {
            $name = $parameter->getName();
            $propertyNames[] = $name;
        }

        return $propertyNames;
    }

    // 获取属性信息 [name => type|class]
    protected static function getPropertyInfos()
    {
        $class = get_called_class();
        $reflectionClass = new ReflectionClass($class);

        // 构造函数
        $constructor = $reflectionClass->getConstructor();
        if (is_null($constructor)) {
            return [];
        }

        // 获取参数
        $parameters = $constructor->getParameters();
        if (empty($parameters)) {
            return [];
        }

        // 获取属性名称和类型
        $result = [];
        foreach($parameters as $parameter) {
            $name = $parameter->getName();
            $type = $parameter->getType();

            $typeName = "mixed";
            if ($type instanceof ReflectionNamedType) {
                $typeName = $type->getName();
            }
            $result[$name] = $typeName;
        }

        return $result;
    }

    // 通过数组创建模型
    public static function build(array $data)
    {
        $data = static::dataHandle($data);

        $class = get_called_class();
        $infos = static::getPropertyInfos();
        if (empty($infos)) {
            return new $class();
        }

        $rules = static::rules();

        $args = [];
        foreach ($infos as $name => $type) {
            if (isset($data[$name])) {
                if (isset($rules[$name]) && !Validator::is($data[$name], $rules[$name])) {
                    throw new Exception("Validate property failed! {$class}::{$name}");
                }
                $value = $data[$name];
            } else {
                // 设置默认值
                switch ($type) {
                    case "string":
                        $value = "";
                        break;
                    case "int":
                    case "float":
                        $value = 0;
                        break;
                    case "array":
                        $value = [];
                        break;
                    default:
                        throw new Exception("Missing property value! {$class}::{$name}");
                }
            }

            $args[] = $value;
        }

        return new $class(...$args);
    }

    /**
     * 批量创建模型
     *
     * @param array   $items  // 二维数组
     * @param string  $key    // 主键属性名
     * @param boolean $unique // 主键是否唯一
     * @return array
     */
    public static function buildMulti(array $items, string $key = "", bool $unique = true)
    {
        $models = [];
        foreach ($items as $data) {
            $model = static::build($data);
            if ($key == "") {
                $models[] = $model;
            } else {
                $value = $model[$key];
                if ($unique) {
                    $models[$value] = $model;
                } else {
                    $models[$value][] = $model;
                }
            }
        }
        return $models;
    }

    // 数据库字段 对应 转换成属性名
    protected static function dataMapper()
    {
        return [];
    }

    // 数据处理
    protected static function dataHandle(array $data)
    {
        return $data;
    }

    // 数据预处理
    final protected static function dataPrepareHandle(array $data)
    {
        $mapper = static::dataMapper();

        // 获取字段转换的数组
        $transforms = [];
        if (!empty($mapper)) {
            foreach ($mapper as $key => $targetKey) {
                if (isset($data[$key])) {
                    $transforms[$targetKey] = $data[$key];
                }
            }
        }

        // 合并结果
        foreach ($transforms as $key => $value) {
            $data[$key] = $value;
        }

        // 预处理数据
        return $data;
    }
}
