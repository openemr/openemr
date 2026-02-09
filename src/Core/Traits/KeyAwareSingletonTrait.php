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
 *   class ClassWithConstructorArguments {
 *       use KeyAwareSingletonTrait;
 *       protected static function createInstance(string|int $key): static { return new static($key, $dependency1, $dependency2); }
 *       public function __construct(string|int $key, private readonly object $dependency1, private readonly object $dependency2) {}
 *   }
 *   $object = ClassWithoutConstructorArguments::getInstanceByKey($userId);
 *
 * @phpstan-template TKey
 */
trait KeyAwareSingletonTrait
{
    /** @var array<class-string<static>, array<TKey, static>> */
    private static array $instances = [];

    final public function __clone()
    {
        throw new LogicException(sprintf(
            'Cloning of %s is not allowed.',
            static::class,
        ));
    }

    /**
     * @phpstan-param TKey $key
     */
    public static function getInstanceByKey($key): static
    {
        if (!isset(static::$instances[static::class][$key])) {
            self::$instances[static::class][$key] = static::createInstance($key);
        }

        return static::$instances[static::class][$key];
    }

    /**
     * @phpstan-param TKey $key
     */
    abstract protected static function createInstance($key): static;
}
