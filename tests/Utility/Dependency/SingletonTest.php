<?php

namespace Tests\JoeyCumines\Phelp\Utility\Dependency;

use JoeyCumines\Phelp\Utility\Dependency\Singleton;
use PHPUnit\Framework\TestCase;

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

    public function testGetInstanceAssertionError()
    {
        $this->expectException(\Error::class);
        DummySingletonExtendedNoUse::getInstance();
    }
}

/*
 * Test Resources
 */

class DummySingleton
{
    use Singleton {
        getInstance as public;
    }
}

class DummySingletonExtended extends DummySingleton
{
    use Singleton {
        getInstance as public;
    }
}

class DummySingletonExtendedNoUse extends DummySingleton
{
}
