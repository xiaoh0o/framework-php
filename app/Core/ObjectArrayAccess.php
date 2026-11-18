<?php
namespace App\Core;

use ArrayAccess;
use Exception;

/**
 * 实现数组访问
 */
abstract class ObjectArrayAccess implements ArrayAccess
{
    /**
     * 获取属性值
     * 
     * 注意：子类的属性必须声明为 public 或 protected，
     * private 属性在父类作用域中不可访问，__get 会触发 Error。
     *
     * @param string $name
     * @return mixed
     */
    public function __get(string $name): mixed
    {
        if (property_exists($this, $name)) {
            return $this->$name;
        }
        throw new Exception("Object property does not exist! [". get_class($this) . "::{$name}]");
    }

    /**
     * 设置属性，被禁止
     *
     * @param string $name
     * @param mixed $value
     */
    public function __set(string $name, mixed $value): void
    {
        throw new Exception("Object property is readonly! [". get_class($this) ."::{$name}]");
    }

    /**
     * 判断属性是否存在 (可以为null)
     *
     * @param string $name
     * @return boolean
     */
    public function __isset(string $name): bool
    {
        return property_exists($this, $name);
    }

    /**
     * 移除属性 被禁止
     *
     * @param string $name
     */
    public function __unset(string $name): void
    {
        throw new Exception("Object property is readonly! [". get_class($this) ."::{$name}]");
    }

    /**
     * 实现 ArrayAccess
     */
    public function offsetExists(mixed $name): bool
    {
        if (!is_string($name)) {
            throw new Exception("Property name must be string! [". gettype($name) ."]");
        }
        return property_exists($this, $name);
    }

    public function offsetGet(mixed $name): mixed
    {
        if (!is_string($name)) {
            throw new Exception("Property name must be string! [". gettype($name) ."]");
        }
        return $this->__get($name);
    }

    public function offsetSet(mixed $name, mixed $value): void
    {
        if (!is_string($name)) {
            throw new Exception("Property name must be string! [". gettype($name) ."]");
        }
        $this->__set($name, $value);
    }

    public function offsetUnset(mixed $name): void
    {
        if (!is_string($name)) {
            throw new Exception("Property name must be string! [". gettype($name) ."]");
        }
        $this->__unset($name);
    }
}
