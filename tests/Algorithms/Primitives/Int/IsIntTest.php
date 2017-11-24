<?php

namespace Tests\JoeyCumines\Phelp\Algorithms\Primitives\Int;

use JoeyCumines\Phelp\Algorithms\Primitives\Int\IsInt;
use JoeyCumines\Phelp\Utility\Logging\Output\GetVarDump;
use JoeyCumines\Phelp\Utility\Testing\AssertExactEquals;
use PHPUnit\Framework\TestCase;

class IsIntTest extends TestCase
{
    use IsInt;
    use GetVarDump;
    use AssertExactEquals;

    /** @var IsInt */
    protected $target;

    protected function setUp()
    {
        parent::setUp();
        $this->target = $this;
    }

    /**
     * @param mixed $value
     * @param bool $expected
     *
     * @dataProvider isIntProvider
     */
    public function testIsInt($value, $expected)
    {
        $actual = $this->target->isInt($value);
        $this->assertExactEquals(
            $expected,
            $actual,
            sprintf(
                'failed asserting output isInt for value:%s%s',
                PHP_EOL,
                $this->getVarDump($value)
            )
        );
    }

    public function isIntProvider()
    {
        return [
            'actual int' => [12412, true],
            'actual int -' => [12412, true],
            'actual int 0' => [0, true],
            'empty string' => ['', false],
            'exponent string' => ['10E2', false],
            'float string' => ['10.23', false],
            'int string 0' => ['0', true],
            'int string -0' => ['-0', false],
            'int string' => ['123699', true],
            'int string -' => ['-213', true],
            'int string +' => ['+124', false],
            'int string huge' => ['12348818248121123124214777732132321', false],
            'float, yes' => [1213214.0, true],
            'float, no' => [1213214.00001, false],
            'float, 0' => [0.0, true],
            'float, many zero, no' => [14.00000000001, false],
            'float, int max bound check pass' => [(float)PHP_INT_MAX - 10000, true],
            'float, int max bound check fail' => [(float)PHP_INT_MAX + 10000, false],
            'float, int min bound check pass' => [(float)PHP_INT_MIN + 10000, true],
            'float, int min bound check fail' => [(float)PHP_INT_MIN - 10000, false],
            'object' => [new \stdClass(), false],
            'null' => [null, false],
            'true' => [true, false],
            'false' => [false, false]
        ];
    }
}
