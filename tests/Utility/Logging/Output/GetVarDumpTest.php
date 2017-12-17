<?php

namespace Tests\JoeyCumines\Phelp\Utility\Logging\Output;

use JoeyCumines\Phelp\Utility\Logging\Output\GetVarDump;
use PHPUnit\Framework\TestCase;

class GetVarDumpTest extends TestCase
{
    use GetVarDump;

    /** @var GetVarDump */
    protected $target;

    protected function setUp()
    {
        parent::setUp();
        $this->target = $this;
    }

    private function standardizeStringMultiPlatform($value)
    {
        return preg_replace('/[\\s]+/u', '', $value);
    }

    /**
     * @param $input
     * @param $expected
     *
     * @dataProvider getVarDumpProvider
     */
    public function testGetVarDump($input, $expected)
    {
        $actual = $this->target->getVarDump($input);

        $this->assertEquals(
            $this->standardizeStringMultiPlatform($expected),
            $this->standardizeStringMultiPlatform($actual)
        );

        $this->assertEquals($actual, trim($actual));
    }

    public function getVarDumpProvider()
    {
        return [
            [
                123,
                <<<'EOT'
int(123)
EOT
            ],
            [
                'a string value',
                <<<'EOT'
string(14) "a string value"
EOT
            ],
            [
                [1, 2, 3],
                sprintf(
                    <<<'EOT'
array(3) {
  [0] =>
  int(1)
  [1] =>
  int(2)
  [2] =>
  int(3)
}
EOT
                )
            ],
            [
                null,
                <<<'EOT'
NULL
EOT
            ],
        ];
    }
}
