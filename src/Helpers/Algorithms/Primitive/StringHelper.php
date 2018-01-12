<?php

namespace JoeyCumines\Phelp\Helpers\Algorithms\Primitive;

use JoeyCumines\Phelp\Utility\Dependency\Singleton;

/**
 * Class StringHelper
 *
 * @package JoeyCumines\Phelp\Helpers\Algorithms\Primitive
 *
 * Phelp helper class, auto-generated from 2 Trait implementation(s) at 2018-01-12 19:04 AEST.
 */
final class StringHelper
{
    use Singleton {
        getInstance as public;
    }
    use \JoeyCumines\Phelp\Algorithms\Primitive\String\Mbstring\NormaliseEncoding {
        \JoeyCumines\Phelp\Algorithms\Primitive\String\Mbstring\NormaliseEncoding::normaliseEncoding as public;
    }
    use \JoeyCumines\Phelp\Algorithms\Primitive\String\Prce\PregSplitKeep {
        \JoeyCumines\Phelp\Algorithms\Primitive\String\Prce\PregSplitKeep::pregSplitKeep as public;
    }
}
