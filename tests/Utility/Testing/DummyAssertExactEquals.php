<?php

namespace Tests\JoeyCumines\Phelp\Utility\Testing;

use JoeyCumines\Phelp\Utility\Testing\AssertExactEquals;

class DummyAssertExactEquals
{
    use AssertExactEquals;

    private $calls = [];

    /**
     * @return array[]
     */
    public function getCalls()
    {
        return $this->calls;
    }

    public function assertTrue($condition, $message = '')
    {
        $this->calls[] = func_get_args();
    }
}
