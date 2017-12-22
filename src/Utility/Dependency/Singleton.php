<?php

namespace JoeyCumines\Phelp\Utility\Dependency;

/**
 * Trait Singleton
 *
 * @package JoeyCumines\Phelp\Utility\Dependency
 *
 * Simple singleton trait, for a class that provides a default constructor, designed to work with extendable classes.
 *
 * NOTE: if you extend a Singleton, you MUST also `use Singleton;`, a requirement that (if enabled) will be checked
 * by PHP assertions, on call to getInstance. Assertions are used for this simply because it's an expensive test.
 */
trait Singleton
{
    /** @var static */
    private static $instance = null;

    /**
     * Get the singleton instance for this class.
     *
     * @return static
     */
    public static function getInstance()
    {
        // assert that `static` did `use Singleton;`, in a ternary to keep it able to be optimised to no cost
        // NOTE: that `var_export` nastiness is due to the scope not being inherited in assert pre PHP 7
        assert(
            version_compare(PHP_VERSION, '7.0.0', '>=') ?
                in_array('JoeyCumines\\Phelp\\Utility\\Dependency\\Singleton', class_uses(static::class), true) :
                "in_array('JoeyCumines\\Phelp\\Utility\\Dependency\\Singleton', class_uses("
                . var_export(static::class, true)
                . "), true)"
        );

        if (null === static::$instance) {
            static::$instance = new static();
        }

        return static::$instance;
    }
}
