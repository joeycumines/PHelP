<?php

namespace Tests\JoeyCumines\Phelp\Algorithms\Primitive\String\Mbstring;

use JoeyCumines\Phelp\Algorithms\Primitive\String\Mbstring\NormaliseEncoding;
use JoeyCumines\Phelp\Utility\Test\AssertExactEquals;
use Tests\JoeyCumines\Phelp\Algorithms\Primitive\String\MbstringTestCase;

class NormaliseEncodingTest extends MbstringTestCase
{
    use NormaliseEncoding;
    use AssertExactEquals;

    /**
     * @param bool $valid
     *
     * @dataProvider normaliseEncodingArgumentsProvider
     */
    public function testNormaliseEncodingArguments($valid/*, $string, $encoding = null, $normaliser = null*/)
    {
        $args = func_get_args();
        array_shift($args);
        try {
            $this->normaliseEncoding(...$args);
            $this->assertTrue($valid);
        } catch (\InvalidArgumentException $e) {
            $this->assertFalse($valid);
        }
    }

    public function normaliseEncodingArgumentsProvider()
    {
        $validEncodingCases = [];
        foreach (mb_list_encodings() as $i => $encoding) {
            if (true === in_array($encoding, ['pass', 'auto'])) {
                continue;
            }
            $validEncodingCases['valid encoding ' . $i] = [
                true,
                '',
                $encoding,
                null,
            ];
        }
        assert(count($validEncodingCases) > 0);
        return array_merge(
            [
                'simplest case' => [
                    true,
                    '',
                    mb_internal_encoding(),
                    null,
                ],
                'bad $string 1' => [
                    false,
                    123,
                    mb_internal_encoding(),
                    null,
                ],
                'bad $string 2' => [
                    false,
                    null,
                    mb_internal_encoding(),
                    null,
                ],
                'bad $string 3' => [
                    false,
                    true,
                    mb_internal_encoding(),
                    null,
                ],
                'bad $string 4' => [
                    false,
                    new \stdClass(),
                    mb_internal_encoding(),
                    null,
                ],
                'bad encoding (auto-detect)' => [
                    false,
                    strrev('汉'),
                    mb_detect_encoding(strrev('汉')),
                    null,
                ],
                'good encoding (auto-detect)' => [
                    true,
                    '汉',
                    mb_detect_encoding('汉'),
                    null,
                ],
                'bad encoding name 1' => [
                    false,
                    '',
                    'not a real encoding',
                    null,
                ],
                'bad encoding name 2' => [
                    false,
                    '',
                    '',
                    null,
                ],
                'bad encoding type 1' => [
                    false,
                    '',
                    123,
                    null,
                ],
                'bad encoding type 2' => [
                    false,
                    '',
                    true,
                    null,
                ],
                'bad encoding type 3' => [
                    false,
                    '',
                    new \stdClass(),
                    null,
                ],
                'valid encoding' => [
                    true,
                    '',
                    'UTF-8',
                    null,
                ],
                'invalid encoding (pass)' => [
                    false,
                    '',
                    'pass',
                    null,
                ],
                'invalid encoding (auto)' => [
                    false,
                    '',
                    'auto',
                    null,
                ],
                'valid normaliser (callable string)' => [
                    true,
                    '',
                    mb_internal_encoding(),
                    'trim',
                ],
                'valid normaliser (closure)' => [
                    true,
                    '',
                    mb_internal_encoding(),
                    function ($segment) {
                        return $segment;
                    },
                ],
                'invalid normaliser 1' => [
                    false,
                    '',
                    mb_internal_encoding(),
                    123,
                ],
                'invalid normaliser 2' => [
                    false,
                    '',
                    mb_internal_encoding(),
                    true,
                ],
                'invalid normaliser 3' => [
                    false,
                    '',
                    mb_internal_encoding(),
                    new \stdClass(),
                ],
                'invalid normaliser 4' => [
                    false,
                    '',
                    mb_internal_encoding(),
                    'not a real callable',
                ],
            ],
            $validEncodingCases
        );
    }

    public function testNormaliseEncodingUnableToSetSubstitute()
    {
        self::$mb_substitute_character = function () {
            return false;
        };

        $this->expectException(\UnexpectedValueException::class);

        $this->normaliseEncoding('', 'UTF-8');
    }

    public function testNormaliseEncodingSubstituteCallOrderGetSetReset()
    {
        $finalSubCharCallArgs = null;

        $mb_substitute_character = [
            function () {
                $this->assertEquals(0, func_num_args());
                return 44;
            },
            function ($v) {
                $this->assertEquals(1, func_num_args());
                $this->assertEquals('none', $v);
                return true;
            },
            function () use (&$finalSubCharCallArgs) {
                $finalSubCharCallArgs = func_get_args();
                throw new \RuntimeException('test should not fail, this should be caught');
            },
        ];

        self::$mb_substitute_character = function () use (&$mb_substitute_character) {
            $this->assertGreaterThan(0, count($mb_substitute_character));
            return call_user_func_array(array_shift($mb_substitute_character), func_get_args());
        };

        $this->assertExactEquals('', $this->normaliseEncoding('', 'UTF-8'));

        $this->assertCount(0, $mb_substitute_character);

        $this->assertExactEquals([44], $finalSubCharCallArgs);
    }

    /**
     * Ensure that the substitute char correctly resets every time.
     */
    public function testNormaliseEncodingSubstituteCallOrderGetSetResetOnConversionFailure()
    {
        $finalSubCharCallArgs = null;

        $mb_substitute_character = [
            function () {
                $this->assertEquals(0, func_num_args());
                return 44;
            },
            function ($v) {
                $this->assertEquals(1, func_num_args());
                $this->assertEquals('none', $v);
                return true;
            },
            function () use (&$finalSubCharCallArgs) {
                $finalSubCharCallArgs = func_get_args();
                throw new \RuntimeException('test should not fail, this should be caught');
            },
        ];

        self::$mb_substitute_character = function () use (&$mb_substitute_character) {
            $this->assertGreaterThan(0, count($mb_substitute_character));
            return call_user_func_array(array_shift($mb_substitute_character), func_get_args());
        };

        $string = 'SOME_STRING';
        $encoding = 'A_DUMMY_ENCODING';

        self::$mb_list_encodings = function () use ($encoding) {
            $this->assertEquals(0, func_num_args());
            return [$encoding];
        };

        $exception = new \Error('failed to perform conversion');

        self::$mb_convert_encoding = function () use ($exception, $string, $encoding, &$finalSubCharCallArgs) {
            $this->assertExactEquals(
                [
                    $string,
                    $encoding,
                    $encoding,
                ],
                func_get_args()
            );
            $this->assertNull($finalSubCharCallArgs);
            throw $exception;
        };

        try {
            $this->normaliseEncoding($string, $encoding);
            $this->fail('expected an exception');
        } catch (\Error $e) {
            $this->assertExactEquals($exception, $e);
        }

        $this->assertCount(0, $mb_substitute_character);

        $this->assertExactEquals([44], $finalSubCharCallArgs);
    }

    // TODO: write a few simple black-box test cases

    // TODO: write mocked out logic test case, to better test the diff section
}
