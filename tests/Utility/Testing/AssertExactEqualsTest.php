<?php

namespace Tests\JoeyCumines\Phelp\Utility\Testing;

use JoeyCumines\Phelp\Utility\Logging\Output\GetVarDump;
use JoeyCumines\Phelp\Utility\Testing\AssertExactEquals;
use PHPUnit\Framework\TestCase;

class AssertExactEqualsTest extends TestCase
{
    use GetVarDump;

    /**
     * @param $expected
     * @param $actual
     * @param $message
     * @param $lineLength
     * @param $equal
     *
     * @dataProvider assertExactEqualsProvider
     */
    public function testAssertExactEquals($expected, $actual, $message, $lineLength, $equal)
    {
        $case = new class
        {
            use AssertExactEquals;

            private $calls = [];

            /**
             * @return array[]
             */
            public function getCalls(): array
            {
                return $this->calls;
            }

            public function assertTrue($condition, $message = '')
            {
                $this->calls[] = func_get_args();
            }
        };

        ob_start();
        $case->assertExactEquals($expected, $actual, $message, $lineLength);
        $output = ob_get_clean();

        $calls = $case->getCalls();
        $this->assertCount(1, $calls, 'unexpected number of calls to assertTrue');
        [$callCondition, $callMessage] = reset($calls);
        $this->assertEquals($equal, $callCondition, "Unexpected evaluation of assertExactEquals: \n" . $callMessage);
        $this->assertTrue(is_bool($callCondition));

        // there should never be any junk printed to stdout
        $this->assertEquals('', $output);

        // validate the message
        if (1 > $lineLength) {
            $this->assertEquals($message, $callMessage);
        } else {
            // count the max number items per line
            foreach (explode(PHP_EOL, $callMessage) as $line) {
                $this->assertTrue(is_string($line));
                $this->assertLessThanOrEqual($lineLength, strlen($line), $line);
            }

            $trimmedMessage = trim((string)$message);
            $this->assertTrue(
                '' === $trimmedMessage ||
                0 === strpos(ltrim(str_replace(PHP_EOL . '> ', '', $callMessage), "\n\r #"), $trimmedMessage . PHP_EOL),
                ltrim(str_replace(PHP_EOL . '> ', '', $callMessage), "\n\r #") . "\nVS\n" . $trimmedMessage . PHP_EOL
            );
            if (true === $callCondition) {
                // case 1 - no need to do any var_dump, so there is only $message
                $this->assertEquals($message, $callMessage);
            } else if (1 !== $lineLength) {
                $noWrapMessage = str_replace(PHP_EOL . '# ', PHP_EOL, str_replace(PHP_EOL . '> ', '', $callMessage));

                // case 2 - there was a failure, so a `var_dump` of each variable was performed
                $expectedInd = strpos($noWrapMessage, $this->getVarDump($expected));
                $this->assertTrue(true === is_int($expectedInd), $noWrapMessage . PHP_EOL . $this->getVarDump($expected));
                $actualInd = strpos($noWrapMessage, $this->getVarDump($actual));
                $this->assertTrue(true === is_int($actualInd));

                $this->assertGreaterThan($expectedInd, $actualInd);
            }
        }
    }

    public function assertExactEqualsProvider()
    {
        $dummyObject1 = new \stdClass();
        $dummyObject2 = new \stdClass();

        return [
            'float equal no message' => [
                1.1,
                1.1000,
                '',
                80,
                true
            ],
            'float unequal with message' => [
                1.2,
                1.20001,
                ' a non empty message ',
                80,
                false
            ],
            'float vs int same number but unequal, with message' => [
                1,
                1.0,
                'float vs string of the same number will be false',
                80,
                false
            ],
            'object equal' => [
                $dummyObject1,
                $dummyObject1,
                '',
                80,
                true
            ],
            'object not equal' => [
                $dummyObject2,
                $dummyObject1,
                '',
                80,
                false
            ],
            'nested array equal' => [
                [[$dummyObject1, $dummyObject2]],
                [[$dummyObject1, $dummyObject2]],
                '',
                80,
                true
            ],
            'nested array not equal' => [
                [[$dummyObject1, $dummyObject2]],
                [[$dummyObject1, $dummyObject1]],
                '',
                80,
                false
            ],
            'booleans unequal no message' => [
                true,
                false,
                '',
                80,
                false
            ],
            'booleans unequal 0 width' => [
                true,
                false,
                '',
                0,
                false
            ],
            'booleans unequal -1 width' => [
                true,
                false,
                '',
                -1,
                false
            ],
            'booleans unequal 1 width' => [
                true,
                false,
                '',
                1,
                false
            ],
            'long word wrapped string, not equals' => [
                <<<'EOT'
One two threasddsa SADIJJI!J(#!SAD DSAOne two threasddsa SADIJJI!J(#!SAD DSAOne two threasddsa SADIJJI!J(#!SAD DSA
One two threasddsa SADIJJI!J(#!SAD DSA
One two threasddsa SADIJJI!J(#!SAD DSA          
                                                            One two threasddsa SADIJJI!J(#!SAD DSA
                                                            
                                                            
EOT
                ,
                false,
                'the message',
                10,
                false
            ],
            'chinese characters, test line length is by mb characters' => [
                1,
                <<<'EOT'
漢字漢字漢字漢字漢字漢字漢字漢字漢字漢字漢字漢字漢字漢字漢字漢字漢字漢字漢字漢字漢字漢字漢字漢字漢字漢字漢字漢字漢字漢字漢字漢字漢字漢字漢字漢字漢字漢字漢字漢字漢字漢字漢字漢字漢字漢字
漢字


Time period
Bronze Age China to present
Parent systems
Oracle Bone Script
Chinese writing
Direction	Left-to-right
ISO 15924	Hani, 500
Unicode alias
Han
This article contains IPA phonetic symbols. Without proper rendering support, you may see question marks, boxes, or other symbols instead of Unicode characters.
Chinese characters
Hanzi.svg
Hanzi (Chinese character) in traditional (left) and simplified form (right)
Chinese name
Traditional Chinese	漢字
Simplified Chinese	汉字
Literal meaning	"Han characters"
[show]Transcriptions
Vietnamese name
Vietnamese	chữ Hán
Chữ Nôm	𡨸漢
Zhuang name
Zhuang	Saw sawndip.svg倱[1][a]
Sawgun
Korean name
Hangul	한자
Hanja	漢字
[show]Transcriptions
Japanese name
Kanji	漢字
Hiragana	かんじ
[show]Transcriptions
Chinese characters
Chinese characters
Scripts
Precursors
Oracle-bone Bronze
Seal (bird-worm large small)
Clerical Regular
Semi-cursive Cursive Flat brush
Type styles
Imitation Song Ming Sans-serif
Properties
Strokes (order)
Radicals Classification
Variants
Character-form standards
Kangxi Dictionary Xin Zixing
General Standard Chinese Characters (PRC)
Graphemes of Commonly-used Chinese Characters (Hong Kong)
Standard Typefaces for Chinese Characters (ROC Taiwan)
Grapheme-usage standards
Graphemic variants
General Standard Characters (PRC)
Jōyō kanji (Japan)
Previous standards
Commonly-used Characters (PRC)
Frequently-used Characters (PRC)
Tōyō kanji (Japan)
Reforms
Chinese
Traditional characters
Simplified characters
(first round second round)
Debate
Japanese
Old (Kyūjitai) New (Shinjitai)
Ryakuji
Sino-Japanese
Differences in Shinjitai and Simplified characters
Korean
Yakja
Singaporean
                           
EOT
                ,
                'the message',
                10,
                false
            ],
            'corrupt message string handles newlines properly' => [
                1,
                2,
                implode(PHP_EOL . PHP_EOL, str_split('漢字漢字漢字漢字漢字漢字漢字漢字漢', 10)),
                10,
                false
            ],
        ];
    }
}
