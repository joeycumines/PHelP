<?php

/*
 * The testing bootstrap code.
 */

define('PHELP_PATH', dirname(dirname(__FILE__)));

// autoloader
require_once(PHELP_PATH . '/vendor/autoload.php');

// polyfill assertion behaviour, for PHP 5.6
if (version_compare(PHP_VERSION, '7.0.0', '<')) {
    assert_options(ASSERT_ACTIVE, 1);
    assert_options(ASSERT_WARNING, 0);
    assert_options(ASSERT_BAIL, 0);
    assert_options(ASSERT_QUIET_EVAL, 0);

    assert_options(
        ASSERT_CALLBACK,
        function ($file, $line, $code, $description = null) {
            $file = (string)$file;
            $line = (int)$line;
            $code = (string)$code;

            if (false === is_string($description)) {
                $description = 'assert(' . $code . ')';
            }

            $error = new \AssertionError($description, 1);

            $getReflectionProperty = function ($class, $property) use (&$getReflectionProperty) {
                if (false === class_exists($class)) {
                    return null;
                }

                try {
                    return new \ReflectionProperty($class, $property);
                } catch (\Exception $e) {
                } catch (\Throwable $e) {
                }

                $class = get_parent_class($class);

                if (true === is_string($class)) {
                    return $getReflectionProperty($class, $property);
                }

                return null;
            };

            $reflectionProperty = $getReflectionProperty(\AssertionError::class, 'file');
            if ($reflectionProperty instanceof \ReflectionProperty) {
                $reflectionProperty->setAccessible(true);
                $reflectionProperty->setValue($error, $file);
            }

            $reflectionProperty = $getReflectionProperty(\AssertionError::class, 'line');
            if ($reflectionProperty instanceof \ReflectionProperty) {
                $reflectionProperty->setAccessible(true);
                $reflectionProperty->setValue($error, $line);
            }

            $reflectionProperty = $getReflectionProperty(\AssertionError::class, 'trace');
            if ($reflectionProperty instanceof \ReflectionProperty) {
                $reflectionProperty->setAccessible(true);
                $trace = $reflectionProperty->getValue($error);
                if (false === is_array($trace)) {
                    $trace = [];
                }
                array_shift($trace);
                $reflectionProperty->setValue($error, $trace);
            }

            throw $error;
        }
    );
}
