<?php

namespace Tests\JoeyCumines\Phelp\Utility\Dependency;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Error\Error as PhpunitError;

class SingletonTest extends TestCase
{
    public function testGetInstance()
    {
        $singletonConstructed = new DummySingleton();

        $singletonInstance = DummySingleton::getInstance();

        $this->assertEquals(DummySingleton::class, get_class($singletonInstance));
        $this->assertTrue($singletonConstructed !== $singletonInstance);
        $this->assertTrue($singletonInstance === DummySingleton::getInstance());

        $extendedInstance = DummySingletonExtended::getInstance();

        $this->assertTrue($singletonInstance !== $extendedInstance);
        $this->assertTrue($extendedInstance === DummySingletonExtended::getInstance());

        $this->assertTrue($singletonInstance === DummySingleton::getInstance());
    }

    /**
     * Ensures that getInstance on a class that did not use the trait directly (but extends one that does) will fail
     * assertions.
     */
    public function testGetInstanceAssert()
    {
        $isOld = version_compare(PHP_VERSION, '7.0.0', '<');

        try {
            DummySingletonExtendedNoUse::getInstance();
            $this->fail('expected assert inside Singleton::getInstance to fail');
        } catch (PhpunitError $e) {
            $this->assertTrue($isOld);
            $this->assertTrue(false !== strpos($e->getMessage(), 'assert('));
        } catch (\AssertionError $e) {
            $this->assertFalse($isOld);
        }
    }
}
