<?php

namespace johninamillion\Git\Tests;

use PHPUnit\Framework\TestCase as BaseTestCase;
use ReflectionClass;
use ReflectionException;

/**
 * Abstract TestCase.
 *
 * @package johninamillion/php-github
 * @extends BaseTestCase
 */
abstract class TestCase extends BaseTestCase
{
    /**
     * Get private property.
     *
     * @param object $object
     * @param string $property
     * @return mixed
     * @throws ReflectionException
     */
    protected function getPrivateProperty(object $object, string $property): mixed
    {
        $reflection = new ReflectionClass($object);
        $prop = $reflection->getProperty($property);
        $prop->setAccessible(true);

        return $prop->getValue($object);
    }

    /**
     * Set private property.
     *
     * @param object $object
     * @param string $property
     * @param mixed  $value
     * @return void
     * @throws ReflectionException
     */
    protected function setPrivateProperty(object $object, string $property, mixed $value): void
    {
        $reflection = new ReflectionClass($object);
        $prop = $reflection->getProperty($property);
        $prop->setAccessible(true);

        $prop->setValue($object, $value);
    }

    /**
     * Invoke method.
     *
     * @param object $object
     * @param string $method
     * @param array  $args
     * @return mixed
     * @throws ReflectionException
     */
    protected function invokeMethod(object $object, string $method, array $args = []): mixed
    {
        $reflection = new ReflectionClass($object);
        $method = $reflection->getMethod($method);
        $method->setAccessible(true);

        return $method->invokeArgs($object, $args);
    }
}
