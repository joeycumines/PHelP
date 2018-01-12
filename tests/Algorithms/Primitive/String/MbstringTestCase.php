<?php

namespace Tests\JoeyCumines\Phelp\Algorithms\Primitive\String;

use PHPUnit\Framework\TestCase;

/**
 * Class MbstringTestCase
 *
 * @package Tests\JoeyCumines\Phelp\Algorithms\Primitive\String
 *
 * Base test class for the "JoeyCumines\Phelp\Algorithms\Primitive\String\Mbstring" namespace, provides monkey
 * patches which can be set via the static properties.
 */
class MbstringTestCase extends TestCase
{
    public static $mb_substitute_character;
    public static $mb_convert_encoding;
    public static $mb_list_encodings;

    public function setUp()
    {
        self::$mb_substitute_character = null;
        self::$mb_convert_encoding = null;
        self::$mb_list_encodings = null;
    }
}

/*
 * Test Resources
 */

namespace JoeyCumines\Phelp\Algorithms\Primitive\String\Mbstring;

use Tests\JoeyCumines\Phelp\Algorithms\Primitive\String\MbstringTestCase;

/*
 * Monkey patches.
 */

function mb_substitute_character()
{
    if (false === is_callable(MbstringTestCase::$mb_substitute_character)) {
        return \mb_substitute_character(...func_get_args());
    }
    return call_user_func_array(MbstringTestCase::$mb_substitute_character, func_get_args());
}

function mb_convert_encoding()
{
    if (false === is_callable(MbstringTestCase::$mb_convert_encoding)) {
        return \mb_convert_encoding(...func_get_args());
    }
    return call_user_func_array(MbstringTestCase::$mb_convert_encoding, func_get_args());
}

function mb_list_encodings()
{
    if (false === is_callable(MbstringTestCase::$mb_list_encodings)) {
        return \mb_list_encodings(...func_get_args());
    }
    return call_user_func_array(MbstringTestCase::$mb_list_encodings, func_get_args());
}
