<?php

/**
 * @see       https://github.com/laminas/laminas-hydrator for the canonical source repository
 * @copyright https://github.com/laminas/laminas-hydrator/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-hydrator/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Laminas\Hydrator\Filter;

use ArrayObject;
use Closure;
use Laminas\Hydrator\Exception\InvalidArgumentException;

use function array_walk;
use function count;
use function is_callable;
use function sprintf;

final class FilterComposite implements FilterInterface
{
    /**
     * Constant to add with "or" condition
     */
    public const CONDITION_OR = 1;

    /**
     * Constant to add with "and" condition
     */
    public const CONDITION_AND = 2;

    /**
     * @var ArrayObject
     */
    protected $andFilter;

    /**
     * @var ArrayObject
     */
    protected $orFilter;

    /**
     * We can pass a list of OR/AND filters through construct
     *
     * @param callable[]|FilterInterface[] $orFilters
     * @param callable[]|FilterInterface[] $andFilters
     * @throws InvalidArgumentException
     */
    public function __construct(array $orFilters = [], array $andFilters = [])
    {
        array_walk($orFilters, Closure::fromCallable([$this, 'validateFilter']));
        array_walk($andFilters, Closure::fromCallable([$this, 'validateFilter']));

        $this->orFilter = new ArrayObject($orFilters);
        $this->andFilter = new ArrayObject($andFilters);
    }

    /**
     * Add a filter to the composite. Has to be indexed with $name in
     * order to identify a specific filter.
     *
     * This example will exclude all methods from the hydration, that starts with 'getService'
     * <code>
     * $composite->addFilter('exclude',
     *     function ($method) {
     *         if (preg_match('/^getService/', $method) {
     *             return false;
     *         }
     *         return true;
     *     }, FilterComposite::CONDITION_AND
     * );
     * </code>
     *
     * @param  callable|FilterInterface $filter
     * @param  int                      $condition Can be either
     *     FilterComposite::CONDITION_OR or FilterComposite::CONDITION_AND
     * @throws InvalidArgumentException
     */
    public function addFilter(string $name, $filter, int $condition = self::CONDITION_OR) : void
    {
        $this->validateFilter($filter, $name);

        if ($condition === self::CONDITION_OR) {
            $this->orFilter[$name] = $filter;
            return;
        }

        if ($condition === self::CONDITION_AND) {
            $this->andFilter[$name] = $filter;
            return;
        }
    }

    /**
     * Check if $name has a filter registered
     */
    public function hasFilter(string $name) : bool
    {
        return isset($this->orFilter[$name]) || isset($this->andFilter[$name]);
    }

    /**
     * Remove a filter from the composition
     */
    public function removeFilter(string $name) : void
    {
        if (isset($this->orFilter[$name])) {
            unset($this->orFilter[$name]);
        }

        if (isset($this->andFilter[$name])) {
            unset($this->andFilter[$name]);
        }
    }

    /**
     * Filter the composite based on the AND and OR condition
     *
     * Will return true if one from the "or conditions" and all from
     * the "and condition" returns true. Otherwise false
     *
     * @param string $property Parameter will be e.g. Parent\Namespace\Class::method
     */
    public function filter(string $property, ?object $instance = null) : bool
    {
        return $this->atLeastOneOrFilterIsTrue($property, $instance)
            && $this->allAndFiltersAreTrue($property, $instance);
    }

    private function atLeastOneOrFilterIsTrue(string $property, ?object $instance = null) : bool
    {
        if (count($this->orFilter) === 0) {
            return true;
        }

        foreach ($this->orFilter as $filter) {
            if ($this->executeFilter($filter, $property, $instance) === true) {
                return true;
            }
        }

        return false;
    }

    private function allAndFiltersAreTrue(string $property, ?object $instance = null) : bool
    {
        if (count($this->andFilter) === 0) {
            return true;
        }

        foreach ($this->andFilter as $filter) {
            if ($this->executeFilter($filter, $property, $instance) === false) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param callable|FilterInterface $filter
     */
    private function executeFilter($filter, string $property, ?object $instance = null) : bool
    {
        if (is_callable($filter)) {
            /** @psalm-var callable(string, ?object):bool $filter */
            return $filter($property, $instance);
        }

        return $filter->filter($property, $instance);
    }

    /**
     * @param mixed $filter Filters should be callable or
     *     FilterInterface instances.
     * @throws InvalidArgumentException if $filter is neither a
     *     callable nor FilterInterface
     */
    private function validateFilter($filter, string $name) : void
    {
        if (! is_callable($filter) && ! $filter instanceof FilterInterface) {
            throw new InvalidArgumentException(sprintf(
                'The value of %s should be either a callable or an instance of %s',
                $name,
                FilterInterface::class
            ));
        }
    }
}
