<?php

/**
 * @see       https://github.com/laminas/laminas-hydrator for the canonical source repository
 * @copyright https://github.com/laminas/laminas-hydrator/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-hydrator/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Laminas\Hydrator\Strategy;

use Laminas\Stdlib\ArrayUtils;

use function array_map;
use function array_reverse;

final class StrategyChain implements StrategyInterface
{
    /**
     * Strategy chain for extraction
     *
     * @var StrategyInterface[]
     */
    private $extractionStrategies;

    /**
     * Strategy chain for hydration
     *
     * @var StrategyInterface[]
     */
    private $hydrationStrategies;

    /**
     * @param StrategyInterface[] $extractionStrategies
     */
    public function __construct(iterable $extractionStrategies)
    {
        $extractionStrategies = ArrayUtils::iteratorToArray($extractionStrategies);
        $this->extractionStrategies = array_map(
            function (StrategyInterface $strategy) {
                // this callback is here only to ensure type-safety
                return $strategy;
            },
            $extractionStrategies
        );

        $this->hydrationStrategies = array_reverse($extractionStrategies);
    }

    /**
     * {@inheritDoc}
     */
    public function extract($value, ?object $object = null)
    {
        foreach ($this->extractionStrategies as $strategy) {
            $value = $strategy->extract($value, $object);
        }

        return $value;
    }

    /**
     * {@inheritDoc}
     */
    public function hydrate($value, ?array $data = null)
    {
        foreach ($this->hydrationStrategies as $strategy) {
            $value = $strategy->hydrate($value, $data);
        }

        return $value;
    }
}
