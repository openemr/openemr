<?php

/**
 * @see       https://github.com/laminas/laminas-hydrator for the canonical source repository
 * @copyright https://github.com/laminas/laminas-hydrator/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-hydrator/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Laminas\Hydrator\NamingStrategy;

use Laminas\Hydrator\Exception;

use function array_flip;
use function array_walk;
use function is_string;

final class MapNamingStrategy implements NamingStrategyInterface
{
    /**
     * @var array<string, string>
     */
    private $extractionMap = [];

    /**
     * @var array<string, string>
     */
    private $hydrationMap = [];

    /**
     * @param array<string, string> $extractionMap
     */
    public static function createFromExtractionMap(array $extractionMap) : self
    {
        $strategy = new self();
        $strategy->extractionMap = $extractionMap;
        $strategy->hydrationMap  = $strategy->flipMapping($extractionMap);
        return $strategy;
    }

    /**
     * @param array<string, string> $hydrationMap
     */
    public static function createFromHydrationMap(array $hydrationMap) : self
    {
        $strategy = new self();
        $strategy->hydrationMap  = $hydrationMap;
        $strategy->extractionMap = $strategy->flipMapping($hydrationMap);
        return $strategy;
    }

    /**
     * @param array<string, string> $extractionMap
     * @param array<string, string> $hydrationMap
     */
    public static function createFromAsymmetricMap(array $extractionMap, array $hydrationMap) : self
    {
        $strategy = new self();
        $strategy->extractionMap = $extractionMap;
        $strategy->hydrationMap  = $hydrationMap;
        return $strategy;
    }

    /**
     * {@inheritDoc}
     */
    public function extract(string $name, ?object $object = null) : string
    {
        return $this->extractionMap[$name] ?? $name;
    }

    /**
     * {@inheritDoc}
     */
    public function hydrate(string $name, ?array $data = null) : string
    {
        return $this->hydrationMap[$name] ?? $name;
    }

    /**
     * Do not allow direct instantiation of this class.
     *
     * Users should use one of the named constructors:
     *
     * - createFromExtractionMap()
     * - createFromHydrationMap()
     * - createFromAsymmetricMap()
     */
    private function __construct()
    {
    }

    /**
     * Safely flip mapping array.
     *
     * @param  string[] $array Array to flip
     * @return string[] Flipped array
     * @throws Exception\InvalidArgumentException if any value of the $array is
     *     a non-string or empty string value or key.
     */
    private function flipMapping(array $array) : array
    {
        array_walk($array, function ($value, $key) {
            if (! is_string($value) || $value === '') {
                throw new Exception\InvalidArgumentException(
                    'Mapping array can not be flipped because of invalid value'
                );
            }

            if (! is_string($key) || $key === '') {
                throw new Exception\InvalidArgumentException(
                    'Mapping array can not be flipped because of invalid key'
                );
            }
        });

        return array_flip($array);
    }
}
