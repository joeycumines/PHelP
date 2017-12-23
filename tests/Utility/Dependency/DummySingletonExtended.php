<?php

namespace Tests\JoeyCumines\Phelp\Utility\Dependency;

use JoeyCumines\Phelp\Utility\Dependency\Singleton;

class DummySingletonExtended extends DummySingleton
{
    use Singleton {
        getInstance as public;
    }
}
