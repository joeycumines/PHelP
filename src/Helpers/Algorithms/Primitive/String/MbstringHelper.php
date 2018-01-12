<?php

namespace JoeyCumines\Phelp\Helpers\Algorithms\Primitive\String;

use JoeyCumines\Phelp\Utility\Dependency\Singleton;

/**
 * Class MbstringHelper
 *
 * @package JoeyCumines\Phelp\Helpers\Algorithms\Primitive\String
 *
 * Phelp helper class, auto-generated from 1 Trait implementation(s) at 2018-01-12 19:04 AEST.
 */
final class MbstringHelper
{
    use Singleton {
        getInstance as public;
    }
    use \JoeyCumines\Phelp\Algorithms\Primitive\String\Mbstring\NormaliseEncoding {
        \JoeyCumines\Phelp\Algorithms\Primitive\String\Mbstring\NormaliseEncoding::normaliseEncoding as public;
    }
}
