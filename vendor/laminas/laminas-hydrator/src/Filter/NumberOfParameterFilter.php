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

use function sprintf;

final class NumberOfParameterFilter implements FilterInterface
{
    /**
     * The number of parameters being accepted
     *
     * @var int
     */
    protected $numberOfParameters;

    /**
     * @param int $numberOfParameters Number of accepted parameters
     */
    public function __construct(int $numberOfParameters = 0)
    {
        $this->numberOfParameters = $numberOfParameters;
    }

    /**
     * @throws InvalidArgumentException
     */
    public function filter(string $property, ?object $instance = null) : bool
    {
        try {
            $reflectionMethod = $instance !== null
                ? new ReflectionMethod($instance, $property)
                : new ReflectionMethod($property);
        } catch (ReflectionException $exception) {
            throw new InvalidArgumentException(sprintf(
                'Method %s does not exist',
                $property
            ));
        }

        return $reflectionMethod->getNumberOfParameters() === $this->numberOfParameters;
    }
}
