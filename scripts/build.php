#!/usr/bin/env php
<?php

/*
Performs the steps required before commit:
- build helpers

If args '-u UUID' are provided, this will exit immediately if the last run immediately preceding this script
had the same UUID value.
 */

const NAMESPACE_PREFIX = 'JoeyCumines\\Phelp';
const NO_HELPER = '@nohelper';
const TIMEZONE = 'Australia/Brisbane';
const FILE_BUFFER_LENGTH = 1024;

/** The UUID string provided by the last run, may be string, unset, or null, if string === was provided. */
const LOCAL_CONFIG_LAST_BUILD_UUID = 'lastBuildUuid';
/** The default values for the local config file. */
const LOCAL_CONFIG_DEFAULTS = [
    LOCAL_CONFIG_LAST_BUILD_UUID => null,
];

define('TIMESTAMP', time());
define('DATETIME_FORMAT_DATE_HOUR_MINUTES', 'Y-m-d H:i T');
define('ROOT_PATH', dirname(dirname(__FILE__)));
define('SOURCE_PATH', realpath(ROOT_PATH . DIRECTORY_SEPARATOR . 'src'));
define('HELPER_PATH', SOURCE_PATH . DIRECTORY_SEPARATOR . 'Helpers');
define('ALGORITHM_PATH', realpath(SOURCE_PATH . DIRECTORY_SEPARATOR . 'Algorithms'));
define('UTILITY_PATH', realpath(SOURCE_PATH . DIRECTORY_SEPARATOR . 'Utility'));
define('LOCAL_CONFIG_PATH', ROOT_PATH . DIRECTORY_SEPARATOR . '.build.json');
// LOCAL_CONFIG - array
// LOCAL_CONFIG_STRLEN - int
// OPTION_UUID - null|string

// looks in the src dir, ignores the directory but doesn't stop traversal downwards
define(
    'IGNORED_HELPER_PATHS',
    [
        SOURCE_PATH,
        HELPER_PATH,
        ALGORITHM_PATH,
        UTILITY_PATH,
    ]
);

date_default_timezone_set(TIMEZONE);

require_once(ROOT_PATH . '/vendor/autoload.php');

var_dump(ROOT_PATH, SOURCE_PATH, HELPER_PATH);

// globals...
/** @var array $localConfig Mutable version of LOCAL_CONFIG, written out on exit. */
$localConfig = null;

/**
 * The script entry point.
 *
 * Has all the config file code to make it easier.
 *
 * @param string[] ...$args
 *
 * @return int
 */
function main(...$args)
{
    global $localConfig;

    parseArgs(...$args);

    // open local config file
    if (false === file_exists(LOCAL_CONFIG_PATH)) {
        file_put_contents(LOCAL_CONFIG_PATH, '{}');
    }

    clearstatcache();

    $localConfigFp = fopen(LOCAL_CONFIG_PATH, 'r+b');
    if (false === is_resource($localConfigFp) || false === flock($localConfigFp, LOCK_EX)) {
        throw new \RuntimeException('unable to open local config file: ' . LOCAL_CONFIG_PATH);
    }

    try {
        // read the local config file into constants
        // LOCAL_CONFIG: array map of the config values
        // LOCAL_CONFIG_STRLEN: the number of bytes in the config file
        $localConfig = '';
        while (true === is_string($buffer = fread($localConfigFp, FILE_BUFFER_LENGTH)) && '' !== $buffer) {
            $localConfig .= $buffer;
        }
        if (0 !== fseek($localConfigFp, 0)) {
            throw new \RuntimeException('unable to reset the local config file pointer to 0');
        }
        define('LOCAL_CONFIG_STRLEN', strlen($localConfig));
        $localConfig = json_decode($localConfig, true);
        if (JSON_ERROR_NONE !== json_last_error()) {
            throw new \RuntimeException('unable to json_decode local config: ' . json_last_error_msg());
        }
        if (false === is_array($localConfig)) {
            throw new \RuntimeException('unexpected data type for parsed local config: ' . gettype($localConfig));
        }
        $localConfig = array_merge(LOCAL_CONFIG_DEFAULTS, $localConfig);
        define('LOCAL_CONFIG', $localConfig);

        // UUID handling
        $localConfig[LOCAL_CONFIG_LAST_BUILD_UUID] = OPTION_UUID;
        if (null !== OPTION_UUID) {
            if (OPTION_UUID === LOCAL_CONFIG[LOCAL_CONFIG_LAST_BUILD_UUID]) {
                // the same uuid was provided as the last run, skip this one
                return 0;
            }
        }

        // build all the helpers
        buildHelpers();
    } finally {
        // write out $localConfig, then unlock + close it
        if (LOCAL_CONFIG !== $localConfig) {
            $localConfig = (string)json_encode($localConfig, JSON_PRETTY_PRINT);
            while (strlen($localConfig) < LOCAL_CONFIG_STRLEN) {
                $localConfig .= PHP_EOL;
            }
            fwrite($localConfigFp, $localConfig);
        }
        flock($localConfigFp, LOCK_UN);
        fclose($localConfigFp);
    }

    return 0;
}

/**
 * @param string[] $args
 */
function parseArgs(...$args)
{
    foreach ($args as $arg) {
        if (false === is_string($arg)) {
            throw new \InvalidArgumentException('expected only string args');
        }
    }

    $argList = [];
    $optionUuid = null;

    // parse options first, filtering the remainder into $argList
    for ($i = 0; $i < count($args); $i++) {
        // -u UUID
        if (null === $optionUuid && '-u' === $args[$i] && array_key_exists($i + 1, $args)) {
            $i++;
            $optionUuid = $args[$i];
            continue;
        }

        // default: the value of $k belongs in $argList
        $argList[] = $args[$i];
    }

    define('OPTION_UUID', $optionUuid);
}

/**
 * TODO: make good
 *
 * @param $path
 * @param bool $useExceptions
 *
 * @return bool
 */
function deleteDirectoryIfExists($path, $useExceptions = true)
{
    if (false === is_dir($path)) {
        return false;
    }

    // https://stackoverflow.com/a/3349792 modified to NOT expand symlinks + actually handle errors
    $it = new \RecursiveDirectoryIterator($path, \RecursiveDirectoryIterator::SKIP_DOTS);
    $fileList = new \RecursiveIteratorIterator($it, \RecursiveIteratorIterator::CHILD_FIRST);

    foreach ($fileList as $file) {
        /** @var \SplFileInfo $file */
        if ($file->isDir()) {
            $previous = null;

            try {
                $success = rmdir($file->getPathname());
            } catch (\Throwable $e) {
                $success = false;
                $previous = $e;
            }

            if (false === $success) {
                if (false === $useExceptions) {
                    return false;
                }

                throw new \RuntimeException(
                    sprintf(
                        'failed to delete directory "%s" as part of deleting directory "%s"',
                        $file->getPathname(),
                        $path
                    ),
                    0,
                    $previous
                );
            }
        } else {
            $previous = null;

            try {
                $success = unlink($file->getPathname());
            } catch (\Throwable $e) {
                $success = false;
                $previous = $e;
            }

            if (false === $success) {
                if (false === $useExceptions) {
                    return false;
                }

                throw new \RuntimeException(
                    sprintf(
                        'failed to delete file "%s" as part of deleting directory "%s"',
                        $file->getPathname(),
                        $path
                    ),
                    0,
                    $previous
                );
            }
        }
    }

    $previous = null;

    try {
        if (true === rmdir($path)) {
            return true;
        }
    } catch (\Throwable $e) {
        $previous = $e;
    }

    if (false === $useExceptions) {
        return false;
    }

    throw new \RuntimeException(
        sprintf(
            'failed to delete directory "%s"',
            $path
        ),
        0,
        $previous
    );
}

/**
 * TODO: make good
 *
 * @param $path
 *
 * @return bool
 */
function removeEmptySubDirectories($path)
{
    $empty = true;
    foreach (glob($path . DIRECTORY_SEPARATOR . "*") as $file) {
        if (in_array(pathinfo($file, PATHINFO_BASENAME), ['.', '..'])) {
            continue;
        }
        $empty &= is_dir($file) && removeEmptySubDirectories($file);
    }
    return $empty && rmdir($path);
}

/**
 * Get a path relative to source, like 'Helpers/ExampleHelper.php'.
 *
 * @param string $path
 *
 * @return string
 *
 * @throws \InvalidArgumentException
 */
function getSourcePath($path)
{
    if (false === is_string($path)) {
        throw new \InvalidArgumentException('$path must be a string');
    }

    $path = realpath($path);

    if (false === is_string($path) || false === file_exists($path)) {
        throw new \InvalidArgumentException('$path must be valid and exist');
    }

    $expectedStart = SOURCE_PATH . DIRECTORY_SEPARATOR;

    if (0 !== strpos($path, $expectedStart) || strlen($path) <= strlen($expectedStart)) {
        throw new \InvalidArgumentException('$path must be a sub directory of source, but was: ' . $path);
    }

    return (string)substr($path, strlen($expectedStart));
}

/**
 * Builds a helper for all valid packages, for every level all the way down to individual helpers for each trait.
 *
 * Traits can be excluded by including "@noHelper" in their doc comment (case insensitive).
 */
function buildHelpers()
{
    deleteDirectoryIfExists(HELPER_PATH);

    mkdir(HELPER_PATH);

    foreach (
        new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator(SOURCE_PATH, \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::SELF_FIRST
        ) as $path
    ) {
        /** @var \SplFileInfo $path */

        $realPath = $path->getPath() . DIRECTORY_SEPARATOR . $path->getBasename();

        if ($realPath !== HELPER_PATH && true === $path->isDir()) {
            mkdir(HELPER_PATH . substr($realPath, strlen(SOURCE_PATH)), 0777, true);
        }

        if (
            true === in_array($realPath, IGNORED_HELPER_PATHS) ||
            0 !== strpos($realPath, SOURCE_PATH) ||
            0 === strpos($realPath, HELPER_PATH)
        ) {
            continue;
        }

        buildHelper($realPath);
    }

    cleanupUnnecessaryHelperChanges();
    removeEmptySubDirectories(HELPER_PATH);
}

/**
 * Creates a helper for a given source path, if there are any valid helper traits that are at or below it.
 *
 * @param string $path
 */
function buildHelper($path)
{
    $traitList = findHelperTraits($path);

    // no helper traits means no helper created
    if (0 === count($traitList)) {
        return;
    }

    $pathInfo = pathinfo(getSourcePath($path));

    $filename = trim((string)($pathInfo['filename']));

    if ('' === $filename) {
        return;
    }

    $dirname = (string)($pathInfo['dirname']);

    if ('' !== $dirname) {
        $dirname = DIRECTORY_SEPARATOR . $dirname;
    }

    // the helper's namespace
    $helperNamespace = NAMESPACE_PREFIX . '\\' . 'Helpers' . str_replace(DIRECTORY_SEPARATOR, '\\', $dirname);

    // the helper's class name
    $helperName = $filename . 'Helper';

    // where it will actually be saved
    $helperPath = HELPER_PATH
        . $dirname
        . DIRECTORY_SEPARATOR
        . $helperName
        . '.php';

//    $traitInfo = implode(
//        ';' . PHP_EOL . '    ',
//        array_map(
//            function (\ReflectionClass $trait) {
//                return $trait->getName();
//            },
//            $traitList
//        )
//    );
//
//    echo <<<EOT
//#+#####+#
//PATH=$path
//NAMESPACE=$helperNamespace
//NAME=$helperName
//PATH=$helperPath
//TRAITS=$traitInfo
//#-#####-#
//
//EOT;

    $fp = fopen($helperPath, 'wb');

    if (false === is_resource($fp)) {
        throw new \RuntimeException('unable to open helper path for writing: ' . $helperPath);
    }

    try {
        fwrite(
            $fp,
            sprintf(
                <<<'EOT'
<?php

namespace %s;

use JoeyCumines\Phelp\Utility\Dependency\Singleton;

/**
 * Class %s
 *
 * @package %s
 *
 * Phelp helper class, auto-generated from %d Trait implementation(s) at %s.
 */
final class %s
{
    use Singleton {
        getInstance as public;
    }

EOT
                ,
                $helperNamespace,
                $helperName,
                $helperNamespace,
                count($traitList),
                (new \DateTime())
                    ->setTimestamp(TIMESTAMP)
                    ->format(DATETIME_FORMAT_DATE_HOUR_MINUTES),
                $helperName
            )
        );

        $seenMethods = [];

        foreach ($traitList as $trait) {
            fwrite($fp, buildHelperTraitUsage($trait, $seenMethods));
        }

        fwrite(
            $fp,
            <<<'EOT'
}

EOT
        );
    } finally {
        fclose($fp);
    }
}

/**
 * @param ReflectionClass $trait
 * @param array $seenMethods
 *
 * @return string
 */
function buildHelperTraitUsage(\ReflectionClass $trait, array &$seenMethods = [])
{
    if (false === isHelperTrait($trait)) {
        return '';
    }

    $methodList = array_filter(
        $trait->getMethods(),
        function (\ReflectionMethod $method) use (&$seenMethods) {
            if (true === $method->isStatic()) {
                return false;
            }

            if (true === containsNoHelper($method->getDocComment())) {
                return false;
            }

            if (true === in_array($method->getName(), $seenMethods)) {
                throw new \RuntimeException(
                    'ERROR: encountered duplicated method '
                    . $method->getDeclaringClass()->getName()
                    . '::'
                    . $method->getName()
                    . PHP_EOL
                    . 'DEBUG TRACE:'
                    . PHP_EOL
                    . debug_backtrace()
                );
            }

            $seenMethods[] = $method->getName();

            return true;
        }
    );

    if (0 === count($methodList)) {
        return '';
    }

    return sprintf(
        <<<'EOT'
    use \%s {%s
    }

EOT
        ,
        $trait->getName(),
        implode(
            '',
            array_map(
                function (\ReflectionMethod $method) use ($trait) {
                    return PHP_EOL
                        . '        \\'
                        . $trait->getName()
                        . '::'
                        . $method->getName()
                        . ' as public;';
                },
                $methodList
            )
        )
    );
}

/**
 * @param string|mixed $string
 *
 * @return bool
 */
function containsNoHelper($string)
{
    if (false === is_string($string)) {
        return false;
    }

    return false !== strpos(strtolower($string), NO_HELPER);
}

/**
 * Identify all helper traits, in a given path, which must be in the src directory, it's recursive.
 *
 * @param string $path
 *
 * @return \ReflectionClass[]
 *
 * @throws \InvalidArgumentException
 */
function findHelperTraits($path)
{
    if (false === is_string($path)) {
        throw new \InvalidArgumentException('$path must be a string');
    }

    if (
        false === file_exists($path) ||
        false === is_string($path = realpath($path)) ||
        false === file_exists($path)
    ) {
        throw new \InvalidArgumentException('missing $path: ' . $path);
    }

    if (true === is_file($path)) {
        $class = getClassViaPath($path);

        if (null === $class || false === isHelperTrait($class)) {
            return [];
        }

        return [$class];
    }

    $result = [];

    foreach (scandir($path) as $subPath) {
        if ('.' === $subPath || '..' === $subPath) {
            continue;
        }

        $subPath = $path . DIRECTORY_SEPARATOR . $subPath;

        foreach (findHelperTraits($subPath) as $class) {
            $result[] = $class;
        }
    }

    return $result;
}

/**
 * Get the fully qualified class name for a source file in a given path, or null.
 *
 * @param string $path
 *
 * @return \ReflectionClass|null
 *
 * @throws \InvalidArgumentException If not a valid path to something in the src dir.
 */
function getClassViaPath($path)
{
    $class = getSourcePath($path);

    if ('php' !== strtolower((string)pathinfo($class, PATHINFO_EXTENSION))) {
        return null;
    }

    $class = pathinfo($class, PATHINFO_DIRNAME) . DIRECTORY_SEPARATOR . pathinfo($class, PATHINFO_FILENAME);

    $class = implode(
        '\\',
        array_filter(
            array_merge(
                [NAMESPACE_PREFIX],
                explode('/', str_replace('\\', '/', $class))
            ),
            function ($value) {
                return '' !== (string)$value;
            }
        )
    );

    try {
        return new \ReflectionClass($class);
    } catch (\Exception $e) {
    } catch (\Throwable $e) {
    }

    return null;
}

/**
 * @param \ReflectionClass $class
 *
 * @return bool
 */
function isHelperTrait(\ReflectionClass $class)
{
    if (false === $class->isTrait()) {
        return false;
    }

    if (true === containsNoHelper($class->getDocComment())) {
        return false;
    }

    return true;
}

/**
 * Resets all built helpers that have just their timestamp updated, to the git HEAD.
 *
 * TODO: refactor if changes required
 */
function cleanupUnnecessaryHelperChanges()
{
    // get diff stats
    $command = sprintf(
        'cd %s && git diff HEAD --stat --stat-width=9999 || exit $?',
        escapeshellarg(ROOT_PATH)
    );
    $code = null;
    ob_start();
    passthru(
        $command,
        $code
    );
    $stats = ob_get_clean();
    if (0 !== $code) {
        throw new \RuntimeException(sprintf('unexpected exit code "%s" for command "%s"', $code, $command));
    }

    $stats = preg_split('/\\R/u', $stats);

    if (false === is_array($stats) || 1 >= count($stats)) {
        throw new \RuntimeException('invalid stats output: ' . $stats);
    }

    // it's valid UTF-8

    foreach ($stats as $stat) {
        $stat = (string)$stat;

        if (1 !== preg_match('/\|\s+2\s+\+-$/u', $stat)) {
            continue;
        }

        $pipeIndex = strrpos($stat, '|');

        if (false === is_int($pipeIndex)) {
            throw new \RuntimeException('bad pipe index for output line: ' . $stat);
        }

        $filePath = ROOT_PATH . DIRECTORY_SEPARATOR . trim(substr($stat, 0, $pipeIndex));

        // moved files are always kept in full, also they are not real file paths so they would require effort
        if (1 === preg_match('/{.* => .*}/u', $filePath)) {
            continue;
        }

        $filePath = realpath($filePath);

        if (false === is_string($filePath) || false === is_file($filePath) || false === is_readable($filePath)) {
            throw new \RuntimeException('unexpected "git diff HEAD --stat --stat-width=9999" output line: ' . $stat);
        }

        // skip if not a helper
        if (
            HELPER_PATH !== substr($filePath, 0, strlen(HELPER_PATH)) ||
            strlen(HELPER_PATH) === strlen($filePath) ||
            1 !== preg_match('/\\.php$/ui', $filePath)
        ) {
            continue;
        }

        // get the diff for the specific file
        $command = sprintf(
            'cd %s && git diff HEAD -- %s || exit $?',
            escapeshellarg(ROOT_PATH),
            escapeshellarg($filePath)
        );
        $code = null;
        ob_start();
        passthru(
            $command,
            $code
        );
        $diff = ob_get_clean();
        if (0 !== $code) {
            throw new \RuntimeException(sprintf('unexpected exit code "%s" for command "%s"', $code, $command));
        }

        // if it has one + and one -, for the timestamp change only, we can reset
        if (
            1 !== preg_match(
                '/^\s*-\s*\* Phelp helper class, auto-generated from \d+ Trait implementation\(s\) at .+\.\s*$/um',
                $diff
            ) ||
            1 !== preg_match(
                '/^\s*\+\s*\* Phelp helper class, auto-generated from \d+ Trait implementation\(s\) at .+\.\s*$/um',
                $diff
            )
        ) {
            continue;
        }

        // reset the helper to HEAD
        $command = sprintf(
            'cd %s && git checkout HEAD -- %s || exit $?',
            escapeshellarg(ROOT_PATH),
            escapeshellarg($filePath)
        );
        $code = null;
        ob_start();
        passthru(
            $command,
            $code
        );
        ob_end_clean();
        if (0 !== $code) {
            throw new \RuntimeException(sprintf('unexpected exit code "%s" for command "%s"', $code, $command));
        }
    }
}

exit(main(...array_slice($argv, 1)));
