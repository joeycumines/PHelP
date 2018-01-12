<?php

namespace JoeyCumines\Phelp\Algorithms\Primitive\String\Mbstring;

trait NormaliseEncoding
{
    /**
     * Validate and return a given $string as a given $encoding*, optionally providing a custom $normaliser to
     * process any invalid segments. This method is designed to be used as a final pass to ensure that a given string
     * is a given $encoding, to gracefully handle the difficult case of partially corrupt or invalid strings.
     *
     * (*) to guarantee 100% valid encoding, if a custom $normaliser is provided, it must return valid $encoding
     *
     * NOTES:
     * - with the default $normaliser, the return value should be guaranteed to be valid for $encoding
     * - the default $normaliser is used when left null, and works like
     *      `return mb_convert_encoding($segment, $encoding, 'pass');`
     * - entire invalid segments will be passed into $normaliser, not just individual bytes
     * - the pseudo-encodings 'pass' and 'auto' MAY NOT be used as $encoding, they will trigger an exception
     *
     * @param string $string The input string, to validate as $encoding
     * @param string $encoding The target encoding, it must be in `mb_list_encodings()`and NOT in ['auto', 'pass']
     * @param callable|null $normaliser The normaliser function to use, null uses mbstring's 'pass', to convert
     *      invalid segments, like `mb_convert_encoding($segment, $encoding, 'pass')`
     *
     * @return string
     *
     * @throws \InvalidArgumentException If any parameters don't match the documented allowed types, or $encoding
     *      is not supported.
     *
     * @link http://php.net/manual/en/function.mb-list-encodings.php
     * @link http://php.net/manual/en/function.mb-convert-encoding.php
     */
    private function normaliseEncoding($string, $encoding, $normaliser = null)
    {
        if (false === is_string($string)) {
            throw new \InvalidArgumentException('$string must be a string');
        }

        if (false === is_string($encoding)) {
            throw new \InvalidArgumentException('$encoding must be a string');
        }

        if ('pass' === $encoding || 'auto' === $encoding) {
            throw new \InvalidArgumentException(
                '$encoding may not be "pass" or "auto", as they are not real encodings, despite being returned by mb_list_encodings()'
            );
        }

        if (false === in_array($encoding, mb_list_encodings(), true)) {
            throw new \InvalidArgumentException(
                '$encoding must be one of the available encodings: ' . implode(', ', mb_list_encodings())
            );
        }

        if (null === $normaliser) {
            // default the $normaliser to one that simply does "pass-through" encoding
            $normaliser = function ($segment) use ($encoding) {
                return mb_convert_encoding($segment, $encoding, 'pass');
            };
        }

        if (false === is_callable($normaliser)) {
            throw new \InvalidArgumentException('$normaliser must be a callable or null');
        }

        // store the original value of mb_substitute_character(), so it can be reset
        $substituteChar = mb_substitute_character();

        // work with 'none' as the value of mb_substitute_character()
        if (true !== mb_substitute_character('none')) {
            throw new \UnexpectedValueException('expected mb_substitute_character("none") to return true');
        }

        try {
            // convert $string to $encoding, which has the effect of removing invalid characters
            $converted = (string)mb_convert_encoding($string, $encoding, $encoding);
        } finally {
            try {
                // reset the substitute character
                mb_substitute_character($substituteChar);
            } catch (\Exception $e) {
            } catch (\Throwable $e) {
            }
        }

        unset($substituteChar);

        // assert that the encoding of $converted is now the $encoding we expect it to be
        assert(
            version_compare(PHP_VERSION, '7.0.0', '>=') ?
                mb_check_encoding($converted, $encoding) :
                'mb_check_encoding($converted, $encoding)'
        );

        // assert that we didn't gain any bytes on conversion (it should be stripping invalid bytes only)
        assert(
            version_compare(PHP_VERSION, '7.0.0', '>=') ?
                strlen($converted) <= strlen($string) :
                'strlen($converted) <= strlen($string)'
        );

        if ($string === $converted) {
            // if we did not strip anything at all we have no extra work to do - the $string was valid $encoding
            return $string;
        }

        // build from $converted and $string, replacing each invalid segment using $normaliser
        // $converted gets processed (valid) runes shifted from it's front, while $string is iterated by byte

        $built = '';

        // in bytes
        $stringIndex = 0;
        $stringLength = (int)strlen($string);

        // $converted is the top-level iteration, then $string, work until BOTH have been fully iterated
        while ('' !== $converted || $stringIndex < $stringLength) {
            // the leftmost character in $converted, and the target for this iteration
            $convertedChar = '';
            // the length of $convertedChar in bytes
            $convertedCharLength = 0;
            // built out of all the yet-to-be-seen invalid bytes before $convertedChar, from $string
            $segment = '';

            if ('' !== $converted) {
                // shift $convertedChar from $converted
                $convertedChar = mb_substr($converted, 0, 1, $encoding);
                if (false === is_string($convertedChar) || '' === $convertedChar) {
                    $convertedChar = (string)substr($converted, 0, 1);
                }
                $convertedCharLength = (int)strlen($convertedChar);
                // assert that we always shifted at least one character
                assert(0 < $convertedCharLength);
                $converted = (string)substr($converted, $convertedCharLength);
            }

            // increment $stringIndex until $convertedChar is reached, building $segment
            while ($stringIndex < $stringLength) {
                if (
                    0 < $convertedCharLength &&
                    $convertedChar === substr($string, $stringIndex, $convertedCharLength)
                ) {
                    // $stringIndex is the start of the valid rune that was just shifted from $converted
                    break;
                }
                // append $stringIndex to $segment, we have yet to find a (non-empty) $convertedChar
                $segment .= $string[$stringIndex];
                $stringIndex++;
            }

            // increment $stringIndex by $convertedCharLength so we can keep pace
            // this needs to happen here, since $convertedChar may be multi-byte
            $stringIndex += $convertedCharLength;

            if ('' !== $segment) {
                // we found an invalid segment, normalise it
                $segment = (string)($normaliser($segment));
            }

            // increment $built, with 1+ bytes from $converted and possibly $string
            $built .= $segment . $convertedChar;
        }

        return $built;
    }
}
