<?php

/**
 * @see       https://github.com/laminas/laminas-hydrator for the canonical source repository
 * @copyright https://github.com/laminas/laminas-hydrator/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-hydrator/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Laminas\Hydrator\Strategy;

use Laminas\Hydrator\Exception;
use Laminas\Hydrator\HydratorInterface;
use ReflectionClass;

use function array_map;
use function class_exists;
use function get_class;
use function gettype;
use function is_array;
use function is_object;
use function sprintf;

class CollectionStrategy implements StrategyInterface
{
    /**
     * @var HydratorInterface
     */
    private $objectHydrator;

    /**
     * @var string
     */
    private $objectClassName;

    /**
     * @throws Exception\InvalidArgumentException
     */
    public function __construct(HydratorInterface $objectHydrator, string $objectClassName)
    {
        if (! class_exists($objectClassName)) {
            throw new Exception\InvalidArgumentException(sprintf(
                'Object class name needs to be the name of an existing class, got "%s" instead.',
                $objectClassName
            ));
        }

        $this->objectHydrator = $objectHydrator;
        $this->objectClassName = $objectClassName;
    }

    /**
     * Converts the given value so that it can be extracted by the hydrator.
     *
     * @param  mixed[] $value The original value.
     * @throws Exception\InvalidArgumentException
     * @return mixed Returns the value that should be extracted.
     */
    public function extract($value, ?object $object = null)
    {
        if (! is_array($value)) {
            throw new Exception\InvalidArgumentException(sprintf(
                'Value needs to be an array, got "%s" instead.',
                is_object($value) ? get_class($value) : gettype($value)
            ));
        }

        return array_map(function ($object) {
            if (! $object instanceof $this->objectClassName) {
                throw new Exception\InvalidArgumentException(sprintf(
                    'Value needs to be an instance of "%s", got "%s" instead.',
                    $this->objectClassName,
                    is_object($object) ? get_class($object) : gettype($object)
                ));
            }

            return $this->objectHydrator->extract($object);
        }, $value);
    }

    /**
     * Converts the given value so that it can be hydrated by the hydrator.
     *
     * @param  mixed[] $value The original value.
     * @throws Exception\InvalidArgumentException
     * @return object[] Returns the value that should be hydrated.
     */
    public function hydrate($value, ?array $data = null)
    {
        if (! is_array($value)) {
            throw new Exception\InvalidArgumentException(sprintf(
                'Value needs to be an array, got "%s" instead.',
                is_object($value) ? get_class($value) : gettype($value)
            ));
        }

        $reflection = new ReflectionClass($this->objectClassName);

        return array_map(function ($data) use ($reflection) {
            return $this->objectHydrator->hydrate(
                $data,
                $reflection->newInstanceWithoutConstructor()
            );
        }, $value);
    }
}
