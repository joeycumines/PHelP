<?php

namespace JoeyCumines\Phelp\Helpers\Utility;

use JoeyCumines\Phelp\Utility\Dependency\Singleton;

/**
 * Class LogHelper
 *
 * @package JoeyCumines\Phelp\Helpers\Utility
 *
 * Phelp helper class, auto-generated from 1 Trait implementation(s) at 2018-01-10 20:40 AEST.
 */
final class LogHelper
{
    use Singleton {
        getInstance as public;
    }
    use \JoeyCumines\Phelp\Utility\Log\GetVarDump {
        \JoeyCumines\Phelp\Utility\Log\GetVarDump::getVarDump as public;
    }
}
