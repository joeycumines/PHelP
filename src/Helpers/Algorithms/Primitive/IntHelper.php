<?php

namespace JoeyCumines\Phelp\Helpers\Algorithms\Primitive;

use JoeyCumines\Phelp\Utility\Dependency\Singleton;

/**
 * Class IntHelper
 *
 * @package JoeyCumines\Phelp\Helpers\Algorithms\Primitive
 *
 * Phelp helper class, auto-generated from 1 Trait implementation(s) at 2018-01-09 23:48 AEST.
 */
final class IntHelper
{
    use Singleton {
        getInstance as public;
    }
    use \JoeyCumines\Phelp\Algorithms\Primitive\Int\IsInt {
        \JoeyCumines\Phelp\Algorithms\Primitive\Int\IsInt::isInt as public;
    }
}
