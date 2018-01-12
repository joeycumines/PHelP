<?php

namespace JoeyCumines\Phelp\Helpers\Algorithms\Primitive\String\Mbstring;

use JoeyCumines\Phelp\Utility\Dependency\Singleton;

/**
 * Class NormaliseEncodingHelper
 *
 * @package JoeyCumines\Phelp\Helpers\Algorithms\Primitive\String\Mbstring
 *
 * Phelp helper class, auto-generated from 1 Trait implementation(s) at 2018-01-09 23:48 AEST.
 */
final class NormaliseEncodingHelper
{
    use Singleton {
        getInstance as public;
    }
    use \JoeyCumines\Phelp\Algorithms\Primitive\String\Mbstring\NormaliseEncoding {
        \JoeyCumines\Phelp\Algorithms\Primitive\String\Mbstring\NormaliseEncoding::normaliseEncoding as public;
    }
}
