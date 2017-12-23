<?php

namespace Tests\JoeyCumines\Phelp\Utility\Dependency;

use JoeyCumines\Phelp\Utility\Dependency\Singleton;

class DummySingleton
{
    use Singleton {
        getInstance as public;
    }
}
