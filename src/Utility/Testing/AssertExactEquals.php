<?php

namespace JoeyCumines\Phelp\Utility\Testing;

/**
 * Trait AssertExactEquals
 * @package JoeyCumines\Phelp\Utility\Testing
 *
 * A trait which gives your test cases access to a very useful exact equals assertion method, which has a formatted
 * output message that uses cleaned `var_dump` for more information.
 */
trait AssertExactEquals
{
    abstract protected function assertTrue($condition, $message = '');

    /**
     * Assert that values are equal using `===`, with detailed nicely formatted error message output that is
     * lazily generated.
     *
     * NOTES:
     * - this will likely butcher multi-byte strings if it has to concat them
     *
     * @param mixed $expected
     * @param mixed $actual
     * @param string $message
     * @param int $lineLength The max line length for the message, if it's < 1 only $message will be used.
     */
    public function assertExactEquals($expected, $actual, $message = '', int $lineLength = 80)
    {
        $equal = $expected === $actual;

        // we can break out early if the test is going to pass, or if the line length was invalid, since we don't
        // have to generate a message in these cases - we still assert though to keep the numbers consistent
        if (true === $equal || 1 > $lineLength) {
            $this->assertTrue($equal, $message);
            return;
        }

        $message = trim((string)$message);

        if ('' !== $message) {
            $message .= PHP_EOL . PHP_EOL;
        }

        $message .= sprintf(
            'Assertion Error: the $actual (%s) value did not exactly equal the $expected (%s) value',
            gettype($actual),
            gettype($expected)
        );

        // generate the message using output buffering and var_dump
        ob_start();
        echo $message . PHP_EOL;
        echo PHP_EOL . 'EXPECTED: ' . PHP_EOL;
        var_dump($expected);
        echo PHP_EOL . 'ACTUAL: ' . PHP_EOL;
        var_dump($actual);
        $message = ob_get_clean();

        // clean up the call traces left by the two var_dump calls (they don't provide anything useful)
        $message = explode(__FILE__, $message);
        for ($x = 1; $x < count($message); $x++) {
            $message[$x] = explode(':', $message[$x]);
            foreach ($message[$x] as $k => $v) {
                if (false === ctype_digit($v) && '' !== $v) {
                    $message[$x][$k] = ltrim($v);
                    break;
                }
                unset($message[$x][$k]);
            }
            $message[$x] = implode(':', $message[$x]);
        }
        $message = trim((string)implode('', $message));

        $template = str_repeat('#', $lineLength - 1);
        $template = $template . PHP_EOL . '#%s' . PHP_EOL . $template;

        $splitMessage = preg_split('~\R~u', $message);

        if (false === is_array($splitMessage)) {
            $splitMessage = explode(
                "\n",
                str_replace(
                    "\r",
                    "\n",
                    str_replace(
                        "\n\r",
                        "\n",
                        str_replace(
                            "\r\n",
                            "\n",
                            $message
                        )
                    )
                )
            );
        }

        // wrap the message in a border to make it more readable, text wrapping long lines
        $message = sprintf(
            $template,
            implode(
                PHP_EOL . '#',
                array_map(
                    function ($line) use ($lineLength) {
                        $indentedLineLength = $lineLength - 3;

                        if (1 > $indentedLineLength) {
                            return '';
                        }

                        $line = str_split((string)$line, $indentedLineLength);

                        if (false === is_array($line) || 0 === count($line)) {
                            return '';
                        }

                        $line = array_values($line);

                        $line[0] = ' ' . $line[0];

                        for ($x = 1; $x < count($line); $x++) {
                            $line[$x] = '> ' . $line[$x];
                        }

                        return implode(PHP_EOL, $line);
                    },
                    $splitMessage
                )
            )
        );

        // perform an assertion which will output the error
        $this->assertTrue($equal, $message);
    }
}
