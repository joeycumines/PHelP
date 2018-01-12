<?php

namespace Tests\JoeyCumines\Phelp\Algorithms\Primitive\String\Prce;

use JoeyCumines\Phelp\Algorithms\Primitive\String\Prce\PregSplitKeep;
use JoeyCumines\Phelp\Utility\Test\AssertExactEquals;
use PHPUnit\Framework\TestCase;

class PregSplitKeepTest extends TestCase
{
    use AssertExactEquals;
    use PregSplitKeep;

    /** @var PregSplitKeep */
    protected $target;

    protected function setUp()
    {
        parent::setUp();
        $this->target = $this;
    }

    /**
     * @param $expected
     *
     * @dataProvider pregSplitKeepProvider
     */
    public function testPregSplitKeep($expected/*, $pattern, $subject, $limit = -1*/)
    {
        $args = func_get_args();
        array_shift($args);
        $args = array_values($args);
        $actual = $this->target->pregSplitKeep(...$args);
        $this->assertExactEquals($expected, $actual);
    }

    public function pregSplitKeepProvider()
    {
        return [
            'match nothing (empty string)' => [
                [
                    ['', null],
                ],
                '/.+/',
                '',
                -1,
            ],
            'simple match' => [
                [
                    ['hello ', 'to'],
                    [' you', null],
                ],
                '/to/',
                'hello to you',
                -1,
            ],
            'multiple match' => [
                [
                    ['hello', ' '],
                    ['to', ' '],
                    ['you', null],
                ],
                '/\\s/',
                'hello to you',
                -1,
            ],
            'multiple match, no limit (test default)' => [
                [
                    ['hello', ' '],
                    ['to', ' '],
                    ['you', null],
                ],
                '/\\s/',
                'hello to you',
            ],
            'multiple match, limited 1' => [
                [
                    ['hello to you', null],
                ],
                '/\\s/',
                'hello to you',
                1,
            ],
            'multiple match, limited 2' => [
                [
                    ['hello', ' '],
                    ['to you', null],
                ],
                '/\\s/',
                'hello to you',
                2,
            ],
            'multi-byte' => [
                [
                    ['⌘', ','],
                    ['漢', ','],
                    ['字', ','],
                    ['', null],
                ],
                '/,/',
                '⌘,漢,字,',
                -1,
            ],
        ];
    }
}
