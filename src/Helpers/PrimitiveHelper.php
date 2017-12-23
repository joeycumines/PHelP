<?php

namespace JoeyCumines\Phelp\Helpers;

use JoeyCumines\Phelp\Algorithms\Primitives\Int\IsInt;
use JoeyCumines\Phelp\Utility\Dependency\Singleton;

/**
 * Class PrimitiveHelper
 *
 * @package JoeyCumines\Phelp\Helpers
 *
 * A grouping of functionality for primitive types or low level (as PHP goes) tools, that don't merit their own
 * category.
 */
class PrimitiveHelper
{
    use Singleton {
        getInstance as public;
    }

    use IsInt {
        isInt as public;
    }
}
