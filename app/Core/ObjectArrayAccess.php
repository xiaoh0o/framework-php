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
     * @param string $name
     * @return mixed
     */
    public function __get(string $name): mixed
    {
        if ($this->__isset($name)) {
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
    public function __set(string $name, mixed $value)
    {
        throw new Exception("Object property is readonly! [". get_class($this) ."::{$name}]");
    }

    /**
     * 判断属性是否存在
     *
     * @param string $name
     * @return boolean
     */
    public function __isset(string $name)
    {
        return property_exists(get_class($this), $name);
    }

    /**
     * 移除属性 被禁止
     *
     * @param string $name
     */
    public function __unset(string $name)
    {
        throw new Exception("Object property is readonly! [". get_class($this) ."::{$name}]");
    }

    /**
     * 实现 ArrayAccess
     */
    public function offsetExists(mixed $name): bool
    {
        if (is_string($name)) {
            return $this->__isset($name);
        }
        throw new Exception("Object property name type error! [{$name}]");
    }

    public function offsetGet(mixed $name): mixed
    {
        if (is_string($name)) {
            return $this->__get($name);
        }
        throw new Exception("Object property name type error! [{$name}]");
    }

    public function offsetSet(mixed $name, $value): void
    {
        if (is_string($name)) {
            $this->__set($name, $value);
        }
        throw new Exception("Object property name type error! [{$name}]");
    }

    public function offsetUnset(mixed $name): void
    {
        if (is_string($name)) {
            $this->__unset($name);
        }
        throw new Exception("Object property name type error! [{$name}]");
    }
}
