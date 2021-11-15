<?php

/**
 * @see       https://github.com/laminas/laminas-hydrator for the canonical source repository
 * @copyright https://github.com/laminas/laminas-hydrator/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-hydrator/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Laminas\Hydrator;

use ReflectionClass;
use ReflectionProperty;

use function get_class;

class ReflectionHydrator extends AbstractHydrator
{
    /**
     * Simple in-memory array cache of ReflectionProperties used.
     *
     * @var ReflectionProperty[][]
     */
    protected static $reflProperties = [];

    /**
     * Extract values from an object
     *
     * {@inheritDoc}
     */
    public function extract(object $object) : array
    {
        $result = [];
        foreach (self::getReflProperties($object) as $property) {
            $propertyName = $this->extractName($property->getName(), $object);
            if (! $this->getCompositeFilter()->filter($propertyName)) {
                continue;
            }

            $value = $property->getValue($object);
            $result[$propertyName] = $this->extractValue($propertyName, $value, $object);
        }

        return $result;
    }

    /**
     * Hydrate $object with the provided $data.
     *
     * {@inheritDoc}
     */
    public function hydrate(array $data, object $object)
    {
        $reflProperties = self::getReflProperties($object);
        foreach ($data as $key => $value) {
            $name = $this->hydrateName($key, $data);
            if (isset($reflProperties[$name])) {
                $reflProperties[$name]->setValue($object, $this->hydrateValue($name, $value, $data));
            }
        }
        return $object;
    }

    /**
     * Get a reflection properties from in-memory cache and lazy-load if
     * class has not been loaded.
     *
     * @return ReflectionProperty[]
     */
    protected static function getReflProperties(object $input) : array
    {
        $class = get_class($input);

        if (isset(static::$reflProperties[$class])) {
            return static::$reflProperties[$class];
        }

        static::$reflProperties[$class] = [];
        $reflClass                      = new ReflectionClass($class);
        $reflProperties                 = $reflClass->getProperties();

        foreach ($reflProperties as $property) {
            $property->setAccessible(true);
            static::$reflProperties[$class][$property->getName()] = $property;
        }

        return static::$reflProperties[$class];
    }
}
