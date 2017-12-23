<?php

namespace JoeyCumines\Phelp\Algorithms\Primitives\Int;

/**
 * Trait IsInt
 *
 * @package JoeyCumines\Phelp\Algorithms\Primitives\Int
 *
 * It's as simple as it's name but not as simple as it seems, this trait provides a way to check if a value is an
 * "int" or not.
 *
 * A value is considered an int if it is:
 *
 * 1. an actual int (primitive)
 * 2. a string, one like `1234` or `-4321` but not `01`, `10E1`, `10.0`, and others that will pass `is_numeric` or
 * `ctype_digit`
 * 3. a float value without any decimals that won't cause integer overflow
 *
 * The commonality here is that they can all be converted between their respective data types without losing any
 * data (this may not be 100% correct for floats, I do not know tbh). Boolean values don't count, since you can only
 * convert 1 and 0 to boolean without losing information.
 */
trait IsInt
{
    /**
     * Check if a given, arbitrary value is a data type that can support the full integer range, and can be
     * converted to and from an integer without the loss of data.
     *
     * @param mixed $value
     *
     * @return bool
     */
    private function isInt($value)
    {
        $type = gettype($value);

        switch ($type) {
            case 'integer':
                return true;
            case 'string':
                return (string)((int)($value)) === $value;
            case 'double':
                $asInt = (int)$value;
                return 0 == ($value - $asInt) && $value == $asInt;
        }

        return false;
    }
}
