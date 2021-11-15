<?php

/**
 * @see       https://github.com/laminas/laminas-zendframework-bridge for the canonical source repository
 * @copyright https://github.com/laminas/laminas-zendframework-bridge/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-zendframework-bridge/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\ZendFrameworkBridge;

use ArrayObject;
use Composer\Autoload\ClassLoader;
use RuntimeException;

use function array_values;
use function class_alias;
use function class_exists;
use function explode;
use function file_exists;
use function interface_exists;
use function spl_autoload_register;
use function strlen;
use function strtr;
use function substr;
use function trait_exists;

/**
 * Alias legacy Zend Framework project classes/interfaces/traits to Laminas equivalents.
 */
class Autoloader
{
    /**
     * Attach autoloaders for managing legacy ZF artifacts.
     *
     * We attach two autoloaders:
     *
     * - The first is _prepended_ to handle new classes and add aliases for
     *   legacy classes. PHP expects any interfaces implemented, classes
     *   extended, or traits used when declaring class_alias() to exist and/or
     *   be autoloadable already at the time of declaration. If not, it will
     *   raise a fatal error. This autoloader helps mitigate errors in such
     *   situations.
     *
     * - The second is _appended_ in order to create aliases for legacy
     *   classes.
     */
    public static function load()
    {
        $loaded = new ArrayObject([]);

        spl_autoload_register(self::createPrependAutoloader(
            RewriteRules::namespaceReverse(),
            self::getClassLoader(),
            $loaded
        ), true, true);

        spl_autoload_register(self::createAppendAutoloader(
            RewriteRules::namespaceRewrite(),
            $loaded
        ));
    }

    /**
     * @return ClassLoader
     * @throws RuntimeException
     */
    private static function getClassLoader()
    {
        if (getenv('COMPOSER_VENDOR_DIR') && file_exists(getenv('COMPOSER_VENDOR_DIR') . '/autoload.php')) {
            return include getenv('COMPOSER_VENDOR_DIR') . '/autoload.php';
        }

        if (file_exists(__DIR__ . '/../../../autoload.php')) {
            return include __DIR__ . '/../../../autoload.php';
        }

        if (file_exists(__DIR__ . '/../vendor/autoload.php')) {
            return include __DIR__ . '/../vendor/autoload.php';
        }

        throw new RuntimeException('Cannot detect composer autoload. Please run composer install');
    }

    /**
     * @return callable
     */
    private static function createPrependAutoloader(array $namespaces, ClassLoader $classLoader, ArrayObject $loaded)
    {
        /**
         * @param  string $class Class name to autoload
         * @return void
         */
        return static function ($class) use ($namespaces, $classLoader, $loaded) {
            if (isset($loaded[$class])) {
                return;
            }

            $segments = explode('\\', $class);

            $i = 0;
            $check = '';

            while (isset($segments[$i + 1], $namespaces[$check . $segments[$i] . '\\'])) {
                $check .= $segments[$i] . '\\';
                ++$i;
            }

            if ($check === '') {
                return;
            }

            if ($classLoader->loadClass($class)) {
                $legacy = $namespaces[$check]
                    . strtr(substr($class, strlen($check)), [
                        'ApiTools' => 'Apigility',
                        'Mezzio' => 'Expressive',
                        'Laminas' => 'Zend',
                    ]);
                class_alias($class, $legacy);
            }
        };
    }

    /**
     * @return callable
     */
    private static function createAppendAutoloader(array $namespaces, ArrayObject $loaded)
    {
        /**
         * @param  string $class Class name to autoload
         * @return void
         */
        return static function ($class) use ($namespaces, $loaded) {
            $segments = explode('\\', $class);

            if ($segments[0] === 'ZendService' && isset($segments[1])) {
                $segments[0] .= '\\' . $segments[1];
                unset($segments[1]);
                $segments = array_values($segments);
            }

            $i = 0;
            $check = '';

            // We are checking segments of the namespace to match quicker
            while (isset($segments[$i + 1], $namespaces[$check . $segments[$i] . '\\'])) {
                $check .= $segments[$i] . '\\';
                ++$i;
            }

            if ($check === '') {
                return;
            }

            $alias = $namespaces[$check]
                . strtr(substr($class, strlen($check)), [
                    'Apigility' => 'ApiTools',
                    'Expressive' => 'Mezzio',
                    'Zend' => 'Laminas',
                    'AbstractZendServer' => 'AbstractZendServer',
                    'ZendServerDisk' => 'ZendServerDisk',
                    'ZendServerShm' => 'ZendServerShm',
                    'ZendMonitor' => 'ZendMonitor',
                ]);

            $loaded[$alias] = true;
            if (class_exists($alias) || interface_exists($alias) || trait_exists($alias)) {
                class_alias($alias, $class);
            }
        };
    }
}
