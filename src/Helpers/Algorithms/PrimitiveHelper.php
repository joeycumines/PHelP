<?php

namespace JoeyCumines\Phelp\Helpers\Algorithms;

use JoeyCumines\Phelp\Utility\Dependency\Singleton;

/**
 * Class PrimitiveHelper
 *
 * @package JoeyCumines\Phelp\Helpers\Algorithms
 *
 * Phelp helper class, auto-generated from 3 Trait implementation(s) at 2018-01-12 19:04 AEST.
 */
final class PrimitiveHelper
{
    use Singleton {
        getInstance as public;
    }
    use \JoeyCumines\Phelp\Algorithms\Primitive\Int\IsInt {
        \JoeyCumines\Phelp\Algorithms\Primitive\Int\IsInt::isInt as public;
    }
    use \JoeyCumines\Phelp\Algorithms\Primitive\String\Mbstring\NormaliseEncoding {
        \JoeyCumines\Phelp\Algorithms\Primitive\String\Mbstring\NormaliseEncoding::normaliseEncoding as public;
    }
    use \JoeyCumines\Phelp\Algorithms\Primitive\String\Prce\PregSplitKeep {
        \JoeyCumines\Phelp\Algorithms\Primitive\String\Prce\PregSplitKeep::pregSplitKeep as public;
    }
}
