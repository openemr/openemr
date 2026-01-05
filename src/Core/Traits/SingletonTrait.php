<?php

/**
 * @package   OpenEMR
 *
 * @link      http://www.open-emr.org
 * @link      https://opencoreemr.com
 *
 * @author    Igor Mukhin <igor.mukhin@gmail.com>
 * @copyright Copyright (c) 2025 OpenCoreEMR Inc
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Core\Traits;

use LogicException;

/**
 * Usage:
 *   class ClassWithoutConstructorArguments {
 *       use SingletonTrait;
 *   }
 *
 *   class ClassWithConstructorArguments {
 *       use SingletonTrait;
 *       protected static function createInstance(): static { return new static($argumentA, $argumentB); }
 *   }
 */
trait SingletonTrait
{
    /** @var array<class-string<static>, static> */
    private static array $instances = [];

    final public function __clone()
    {
        throw new LogicException(sprintf(
            'Cloning of %s is not allowed.',
            static::class,
        ));
    }

    public static function getInstance(): static
    {
        if (!isset(static::$instances[static::class])) {
            self::$instances[static::class] = static::createInstance();
        }

        return static::$instances[static::class];
    }

    protected static function createInstance(): static
    {
        return new static(); // @phpstan-ignore-line new.static
    }
}
