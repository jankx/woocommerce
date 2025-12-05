<?php

namespace Jankx\WooCommerce\Tests\Helpers;

use PHPUnit\Framework\TestCase as BaseTestCase;

/**
 * Base Test Case
 */
abstract class TestCase extends BaseTestCase
{
    /**
     * Setup test environment
     */
    protected function setUp(): void
    {
        parent::setUp();
        WordPressMocks::reset();
    }

    /**
     * Tear down test environment
     */
    protected function tearDown(): void
    {
        WordPressMocks::reset();
        parent::tearDown();
    }

    /**
     * Assert that a class uses a trait
     *
     * @param string $trait
     * @param string|object $class
     */
    protected function assertClassUsesTrait(string $trait, $class): void
    {
        $uses = class_uses($class);
        $this->assertContains($trait, $uses, "Class does not use trait {$trait}");
    }

    /**
     * Assert that a class implements an interface
     *
     * @param string $interface
     * @param string|object $class
     */
    protected function assertClassImplementsInterface(string $interface, $class): void
    {
        $implements = class_implements($class);
        $this->assertContains($interface, $implements, "Class does not implement {$interface}");
    }

    /**
     * Assert that a class extends another class
     *
     * @param string $parent
     * @param string|object $class
     */
    protected function assertClassExtends(string $parent, $class): void
    {
        $this->assertInstanceOf($parent, is_object($class) ? $class : new $class());
    }

    /**
     * Get protected/private property value
     *
     * @param object $object
     * @param string $property
     * @return mixed
     */
    protected function getPrivateProperty($object, string $property)
    {
        $reflection = new \ReflectionClass($object);
        $property = $reflection->getProperty($property);
        $property->setAccessible(true);
        return $property->getValue($object);
    }

    /**
     * Set protected/private property value
     *
     * @param object $object
     * @param string $property
     * @param mixed $value
     */
    protected function setPrivateProperty($object, string $property, $value): void
    {
        $reflection = new \ReflectionClass($object);
        $property = $reflection->getProperty($property);
        $property->setAccessible(true);
        $property->setValue($object, $value);
    }

    /**
     * Call protected/private method
     *
     * @param object $object
     * @param string $method
     * @param array $args
     * @return mixed
     */
    protected function callPrivateMethod($object, string $method, array $args = [])
    {
        $reflection = new \ReflectionClass($object);
        $method = $reflection->getMethod($method);
        $method->setAccessible(true);
        return $method->invokeArgs($object, $args);
    }
}

