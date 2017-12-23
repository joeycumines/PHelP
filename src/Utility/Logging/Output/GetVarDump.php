<?php

namespace JoeyCumines\Phelp\Utility\Logging\Output;

/**
 * Trait GetVarDump
 *
 * @package JoeyCumines\Phelp\Utility\Logging\Output
 *
 * Get the output of `var_dump` as a string, which is very useful given it is one of the most reliable (primitive)
 * means of gathering information about a variable.
 */
trait GetVarDump
{
    /**
     * Get the output of `var_dump` as a string, stripped of the file / line info (it's useless in this case).
     *
     * NOTES:
     * - this method is intended for debugging purposes, not for runtime use in a production environment
     * - makes use of output buffering (`ob_start`, etc), so anything that precludes it's use precludes use of this
     *
     * @param mixed $value
     *
     * @return string
     */
    private function getVarDump($value)
    {
        ob_start();
        var_dump($value);
        $result = (string)ob_get_clean();

        /*
        The value of $result should be formatted similar to this:

        ```
        /some/file/path/GetVarDump.php:28:
        array(3) {
          [0] =>
          int(1)
          [1] =>
          int(2)
          [2] =>
          int(3)
        }

        ```

        So we use the following rules to strip the "/some/file/path/GetVarDump.php:28:" part + trailing newline:

        - if the start of $result is not __FILE__, fallback to looking for `realpath(__FILE__)`
        - if neither prefixes can be matched, skip to returning the trimmed value
        - offset the result to start after the second `:`, after the prefix
        - return the offset and trimmed value
         */

        $prefix = null;

        $possiblePrefix = (string)__FILE__;
        if ('' !== $possiblePrefix && 0 === strpos($result, $possiblePrefix)) {
            $prefix = $possiblePrefix;
        }

        if (null === $prefix && '' !== $possiblePrefix) {
            $possiblePrefix = realpath($possiblePrefix);
            if (
                true === is_string($possiblePrefix) &&
                '' !== $possiblePrefix &&
                0 === strpos($result, $possiblePrefix)
            ) {
                $prefix = $possiblePrefix;
            }
        }

        if (null !== $prefix) {
            $offset = strpos($result, ':', strlen($prefix));
            if (true === is_int($offset) && 0 <= $offset) {
                $offset = strpos($result, ':', $offset + 1);
                if (true === is_int($offset) && 0 <= $offset) {
                    $result = substr($result, $offset + 1);
                }
            }
        }

        return trim((string)$result);
    }
}
