<?php

namespace JoeyCumines\Phelp\Helpers\Algorithms\Primitive\String;

use JoeyCumines\Phelp\Utility\Dependency\Singleton;

/**
 * Class PrceHelper
 *
 * @package JoeyCumines\Phelp\Helpers\Algorithms\Primitive\String
 *
 * Phelp helper class, auto-generated from 1 Trait implementation(s) at 2018-01-09 23:48 AEST.
 */
final class PrceHelper
{
    use Singleton {
        getInstance as public;
    }
    use \JoeyCumines\Phelp\Algorithms\Primitive\String\Prce\PregSplitKeep {
        \JoeyCumines\Phelp\Algorithms\Primitive\String\Prce\PregSplitKeep::pregSplitKeep as public;
    }
}
