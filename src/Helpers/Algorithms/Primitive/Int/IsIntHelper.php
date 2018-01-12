<?php

namespace JoeyCumines\Phelp\Helpers\Algorithms\Primitive\Int;

use JoeyCumines\Phelp\Utility\Dependency\Singleton;

/**
 * Class IsIntHelper
 *
 * @package JoeyCumines\Phelp\Helpers\Algorithms\Primitive\Int
 *
 * Phelp helper class, auto-generated from 1 Trait implementation(s) at 2018-01-09 23:48 AEST.
 */
final class IsIntHelper
{
    use Singleton {
        getInstance as public;
    }
    use \JoeyCumines\Phelp\Algorithms\Primitive\Int\IsInt {
        \JoeyCumines\Phelp\Algorithms\Primitive\Int\IsInt::isInt as public;
    }
}
