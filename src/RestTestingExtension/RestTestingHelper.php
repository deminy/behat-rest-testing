<?php

/**
 * @author   Demin Yin <deminy@deminy.net>
 * @license  MIT license
 */

namespace Behat\RestTestingExtension;

/**
 * The RestTestingHelper class.
 */
class RestTestingHelper
{
    /**
     * Finds a property for the given class.
     *
     * @param object|string $class The class instance or name.
     * @param string $name The name of a property.
     * @param boolean $access Make the property accessible?
     * @return ReflectionProperty The property.
     * @throws Exception If the property does not exist.
     */
    public static function findProperty($class, $name, $access = true)
    {
        $reflection = new \ReflectionClass($class);

        while (! $reflection->hasProperty($name)) {
            if (! ($reflection = $reflection->getParentClass())) {
                throw new Exception(sprintf('Class "%s" does not have property "%s" defined.', $class, $name));
            }
        }

        $property = $reflection->getProperty($name);
        $property->setAccessible($access);

        return $property;
    }

    /**
     * Returns the current value of a property.
     *
     * @param object|string $class The class instance or name.
     * @param string $name The name of a property.
     * @return mixed The current value of the property.
     */
    public static function getProperty($class, $name)
    {
        return static::findProperty($class, $name)->getValue(is_object($class) ? $class : null);
    }

    /**
     * Sets the new value of a property.
     *
     * @param object|string $class The class instance or name.
     * @param string $name The name of a property.
     * @param mixed $value The new value.
     * @return void
     */
    public static function setProperty($class, $name, $value)
    {
        static::findProperty($class, $name)->setValue(is_object($class) ? $class : null, $value);
    }

    /**
     * A helper method for testing protected/private static/non-static methods.
     *
     * @param string $className
     * @param string $methodName
     * @return \ReflectionMethod
     */
    public static function getMethod($className, $methodName)
    {
        $class = new \ReflectionClass($className);
        $method = $class->getMethod($methodName);
        $method->setAccessible(true);

        return $method;
    }

    /**
     * Call a protected/private static/non-static method of given class.
     *
     * @param string|object $class
     * @param string $methodName
     * @param array $args
     * @return mixed
     */
    public static function callMethod($class, $methodName, array $args = array())
    {
        $method = self::getMethod((is_object($class) ? get_class($class) : $class), $methodName);
        $class = is_object($class) ? $class : (new $class());

        return $method->invokeArgs($class, $args);
    }
}
