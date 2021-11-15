<?php

/**
 * @see       https://github.com/laminas/laminas-hydrator for the canonical source repository
 * @copyright https://github.com/laminas/laminas-hydrator/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-hydrator/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Laminas\Hydrator;

use function sprintf;

abstract class AbstractHydrator implements
    HydratorInterface,
    Strategy\StrategyEnabledInterface,
    Filter\FilterEnabledInterface,
    NamingStrategy\NamingStrategyEnabledInterface
{
    /**
     * The list with strategies that this hydrator has.
     *
     * @var Strategy\StrategyInterface[]
     */
    protected $strategies = [];

    /**
     * An instance of NamingStrategy\NamingStrategyInterface
     *
     * @var null|NamingStrategy\NamingStrategyInterface
     */
    protected $namingStrategy;

    /**
     * Composite to filter the methods, that need to be hydrated
     *
     * @var null|Filter\FilterComposite
     */
    protected $filterComposite;

    /**
     * Gets the strategy with the given name.
     *
     * @param string $name The name of the strategy to get.
     * @throws Exception\InvalidArgumentException
     */
    public function getStrategy(string $name) : Strategy\StrategyInterface
    {
        if (isset($this->strategies[$name])) {
            return $this->strategies[$name];
        }

        if ($this->hasNamingStrategy()
            && ($hydrated = $this->getNamingStrategy()->hydrate($name))
            && isset($this->strategies[$hydrated])
        ) {
            return $this->strategies[$hydrated];
        }

        if (! isset($this->strategies['*'])) {
            throw new Exception\InvalidArgumentException(sprintf(
                '%s: no strategy by name of "%s", and no wildcard strategy present',
                __METHOD__,
                $name
            ));
        }

        return $this->strategies['*'];
    }

    /**
     * Checks if the strategy with the given name exists.
     *
     * @param string $name The name of the strategy to check for.
     */
    public function hasStrategy(string $name) : bool
    {
        if (isset($this->strategies[$name])) {
            return true;
        }

        if ($this->hasNamingStrategy()
            && isset($this->strategies[$this->getNamingStrategy()->hydrate($name)])
        ) {
            return true;
        }

        return isset($this->strategies['*']);
    }

    /**
     * Adds the given strategy under the given name.
     *
     * @param string $name The name of the strategy to register.
     * @param Strategy\StrategyInterface $strategy The strategy to register.
     */
    public function addStrategy(string $name, Strategy\StrategyInterface $strategy) : void
    {
        $this->strategies[$name] = $strategy;
    }

    /**
     * Removes the strategy with the given name.
     *
     * @param string $name The name of the strategy to remove.
     */
    public function removeStrategy(string $name) : void
    {
        unset($this->strategies[$name]);
    }

    /**
     * Converts a value for extraction. If no strategy exists the plain value is returned.
     *
     * @param  string      $name   The name of the strategy to use.
     * @param  mixed       $value  The value that should be converted.
     * @param  null|object $object The object is optionally provided as context.
     * @return mixed
     */
    public function extractValue(string $name, $value, ?object $object = null)
    {
        return $this->hasStrategy($name)
            ? $this->getStrategy($name)->extract($value, $object)
            : $value;
    }

    /**
     * Converts a value for hydration. If no strategy exists the plain value is returned.
     *
     * @param  string     $name  The name of the strategy to use.
     * @param  mixed      $value The value that should be converted.
     * @param  null|array $data  The whole data is optionally provided as context.
     * @return mixed
     */
    public function hydrateValue(string $name, $value, ?array $data = null)
    {
        return $this->hasStrategy($name)
            ? $this->getStrategy($name)->hydrate($value, $data)
            : $value;
    }

    /**
     * Convert a name for extraction. If no naming strategy exists, the plain value is returned.
     *
     * @param  string      $name    The name to convert.
     * @param  null|object $object  The object is optionally provided as context.
     * @return string
     */
    public function extractName(string $name, ?object $object = null)
    {
        return $this->hasNamingStrategy()
            ? $this->getNamingStrategy()->extract($name, $object)
            : $name;
    }

    /**
     * Converts a value for hydration. If no naming strategy exists, the plain value is returned.
     *
     * @param  string       $name  The name to convert.
     * @param  null|mixed[] $data  The whole data is optionally provided as context.
     */
    public function hydrateName(string $name, ?array $data = null) : string
    {
        return $this->hasNamingStrategy()
            ? $this->getNamingStrategy()->hydrate($name, $data)
            : $name;
    }

    /**
     * Get the filter instance
     */
    public function getFilter() : Filter\FilterInterface
    {
        return $this->getCompositeFilter();
    }

    /**
     * Add a new filter to take care of what needs to be hydrated.
     * To exclude e.g. the method getServiceLocator:
     *
     * <code>
     * $composite->addFilter("servicelocator",
     *     function ($property) {
     *         list($class, $method) = explode('::', $property);
     *         if ($method === 'getServiceLocator') {
     *             return false;
     *         }
     *         return true;
     *     }, FilterComposite::CONDITION_AND
     * );
     * </code>
     *
     * @param string $name Index in the composite
     * @param callable|Filter\FilterInterface $filter
     */
    public function addFilter(string $name, $filter, int $condition = Filter\FilterComposite::CONDITION_OR) : void
    {
        $this->getCompositeFilter()->addFilter($name, $filter, $condition);
    }

    /**
     * Check whether a specific filter exists at key $name or not
     *
     * @param string $name Index/name in the composite
     */
    public function hasFilter(string $name) : bool
    {
        return $this->getCompositeFilter()->hasFilter($name);
    }

    /**
     * Remove a filter from the composition.
     *
     * To not extract "has" methods, unregister the filter.
     *
     * <code>
     * $filterComposite->removeFilter('has');
     * </code>
     */
    public function removeFilter(string $name) : void
    {
        $this->getCompositeFilter()->removeFilter($name);
    }

    /**
     * Adds the given naming strategy
     *
     * @param NamingStrategy\NamingStrategyInterface $strategy The naming to register.
     */
    public function setNamingStrategy(NamingStrategy\NamingStrategyInterface $strategy) : void
    {
        $this->namingStrategy = $strategy;
    }

    /**
     * Gets the naming strategy.
     *
     * If no naming strategy is registered, registers the
     * `IdentityNamingStrategy`, which acts essentially as a no-op.
     *
     * {@inheritDoc}
     */
    public function getNamingStrategy() : NamingStrategy\NamingStrategyInterface
    {
        if (null === $this->namingStrategy) {
            $this->namingStrategy = new NamingStrategy\IdentityNamingStrategy();
        }
        return $this->namingStrategy;
    }

    /**
     * Checks if a naming strategy exists.
     */
    public function hasNamingStrategy() : bool
    {
        return isset($this->namingStrategy);
    }

    /**
     * Removes the naming strategy
     */
    public function removeNamingStrategy() : void
    {
        $this->namingStrategy = null;
    }

    /**
     * Lazy-load the composite filter instance.
     *
     * If no instance is yet registerd for the $filterComposite property, this
     * method will lazy load one.
     *
     * @throws Exception\DomainException if composed $filterComposite is not a
     *     Filter\FilterComposite instance, nor null.
     */
    protected function getCompositeFilter() : Filter\FilterComposite
    {
        if (! $this->filterComposite) {
            $this->filterComposite = new Filter\FilterComposite();
        }

        return $this->filterComposite;
    }
}
