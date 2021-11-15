<?php

/**
 * @see       https://github.com/laminas/laminas-hydrator for the canonical source repository
 * @copyright https://github.com/laminas/laminas-hydrator/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-hydrator/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Laminas\Hydrator\Filter;

use Laminas\Hydrator\Exception\InvalidArgumentException;
use ReflectionException;
use ReflectionMethod;
use ReflectionParameter;

use function array_filter;
use function sprintf;

/**
 * Filter that includes methods which have no parameters or only optional parameters
 */
final class OptionalParametersFilter implements FilterInterface
{
    /**
     * Map of methods already analyzed
     * by {@see OptionalParametersFilter::filter()},
     * cached for performance reasons
     *
     * @var bool[]
     */
    protected static $propertiesCache = [];

    /**
     * {@inheritDoc}
     *
     * @throws InvalidArgumentException if reflection fails due to the method
     *     not existing.
     */
    public function filter(string $property, ?object $instance = null) : bool
    {
        $cacheName = $instance !== null
            ? (new ReflectionMethod($instance, $property))->getName()
            : $property;

        if (array_key_exists($cacheName, static::$propertiesCache)) {
            return static::$propertiesCache[$cacheName];
        }

        try {
            $reflectionMethod = $instance !== null
                ? new ReflectionMethod($instance, $property)
                : new ReflectionMethod($property);
        } catch (ReflectionException $exception) {
            throw new InvalidArgumentException(sprintf('Method %s does not exist', $property));
        }

        $mandatoryParameters = array_filter(
            $reflectionMethod->getParameters(),
            function (ReflectionParameter $parameter) {
                return ! $parameter->isOptional();
            }
        );

        return static::$propertiesCache[$cacheName] = empty($mandatoryParameters);
    }
}
