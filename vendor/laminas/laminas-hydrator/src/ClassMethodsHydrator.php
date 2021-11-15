<?php

/**
 * @see       https://github.com/laminas/laminas-hydrator for the canonical source repository
 * @copyright https://github.com/laminas/laminas-hydrator/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-hydrator/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Laminas\Hydrator;

use Laminas\Stdlib\ArrayUtils;
use Traversable;

use function get_class;
use function get_class_methods;
use function is_callable;
use function lcfirst;
use function method_exists;
use function property_exists;
use function strpos;
use function substr;
use function ucfirst;

class ClassMethodsHydrator extends AbstractHydrator implements HydratorOptionsInterface
{
    /**
     * Flag defining whether array keys are underscore-separated (true) or camel case (false)
     *
     * @var bool
     */
    protected $underscoreSeparatedKeys = true;

    /**
     * Flag defining whether to check the setter method with method_exists to prevent the
     * hydrator from calling __call during hydration
     *
     * @var bool
     */
    protected $methodExistsCheck = false;

    /**
     * Holds the names of the methods used for hydration, indexed by class::property name,
     * false if the hydration method is not callable/usable for hydration purposes
     *
     * @var string[]|bool[]
     */
    private $hydrationMethodsCache = [];

    /**
     * A map of extraction methods to property name to be used during extraction, indexed
     * by class name and method name
     *
     * @var null[]|string[][]
     */
    private $extractionMethodsCache = [];

    /**
     * @var Filter\FilterInterface
     */
    private $callableMethodFilter;

    /**
     * Define if extract values will use camel case or name with underscore
     */
    public function __construct(bool $underscoreSeparatedKeys = true, bool $methodExistsCheck = false)
    {
        $this->setUnderscoreSeparatedKeys($underscoreSeparatedKeys);
        $this->setMethodExistsCheck($methodExistsCheck);

        $this->callableMethodFilter = new Filter\OptionalParametersFilter();

        $compositeFilter = $this->getCompositeFilter();
        $compositeFilter->addFilter('is', new Filter\IsFilter());
        $compositeFilter->addFilter('has', new Filter\HasFilter());
        $compositeFilter->addFilter('get', new Filter\GetFilter());
        $compositeFilter->addFilter(
            'parameter',
            new Filter\OptionalParametersFilter(),
            Filter\FilterComposite::CONDITION_AND
        );
    }

    /**
     * @param mixed[] $options
     */
    public function setOptions(iterable $options) : void
    {
        if ($options instanceof Traversable) {
            $options = ArrayUtils::iteratorToArray($options);
        }

        if (isset($options['underscoreSeparatedKeys'])) {
            $this->setUnderscoreSeparatedKeys($options['underscoreSeparatedKeys']);
        }

        if (isset($options['methodExistsCheck'])) {
            $this->setMethodExistsCheck($options['methodExistsCheck']);
        }
    }

    public function setUnderscoreSeparatedKeys(bool $underscoreSeparatedKeys) : void
    {
        $this->underscoreSeparatedKeys = $underscoreSeparatedKeys;

        if ($this->underscoreSeparatedKeys) {
            $this->setNamingStrategy(new NamingStrategy\UnderscoreNamingStrategy());
            return;
        }

        if ($this->hasNamingStrategy()) {
            $this->removeNamingStrategy();
            return;
        }
    }

    public function getUnderscoreSeparatedKeys() : bool
    {
        return $this->underscoreSeparatedKeys;
    }

    public function setMethodExistsCheck(bool $methodExistsCheck) : void
    {
        $this->methodExistsCheck = $methodExistsCheck;
    }

    public function getMethodExistsCheck() : bool
    {
        return $this->methodExistsCheck;
    }

    /**
     * Extract values from an object with class methods
     *
     * Extracts the getter/setter of the given $object.
     *
     * {@inheritDoc}
     */
    public function extract(object $object) : array
    {
        $objectClass = get_class($object);
        $isAnonymous = false !== strpos($objectClass, '@anonymous');

        if ($isAnonymous) {
            $objectClass = spl_object_hash($object);
        }

        // reset the hydrator's hydrator's cache for this object, as the filter may be per-instance
        if ($object instanceof Filter\FilterProviderInterface) {
            $this->extractionMethodsCache[$objectClass] = null;
        }

        // pass 1 - finding out which properties can be extracted, with which methods (populate hydration cache)
        if (! isset($this->extractionMethodsCache[$objectClass])) {
            $this->extractionMethodsCache[$objectClass] = [];

            $filter  = $this->initCompositeFilter($object);
            $methods = get_class_methods($object);

            foreach ($methods as $method) {
                $methodFqn = $isAnonymous
                    ? $method
                    : $objectClass . '::' . $method;

                if (! $filter->filter($methodFqn, $isAnonymous ? $object : null)
                    || ! $this->callableMethodFilter->filter($methodFqn, $isAnonymous ? $object : null)
                ) {
                    continue;
                }

                $this->extractionMethodsCache[$objectClass][$method] = $this->identifyAttributeName($object, $method);
            }
        }

        $values = [];

        if (null === $this->extractionMethodsCache[$objectClass]) {
            return $values;
        }

        // pass 2 - actually extract data
        foreach ($this->extractionMethodsCache[$objectClass] as $methodName => $attributeName) {
            $realAttributeName          = $this->extractName($attributeName, $object);
            $values[$realAttributeName] = $this->extractValue($realAttributeName, $object->$methodName(), $object);
        }

        return $values;
    }

    private function initCompositeFilter(object $object) : Filter\FilterComposite
    {
        if ($object instanceof Filter\FilterProviderInterface) {
            return new Filter\FilterComposite(
                [$object->getFilter()],
                [new Filter\MethodMatchFilter('getFilter')]
            );
        }

        return $this->getCompositeFilter();
    }

    private function identifyAttributeName(object $object, string $method) : string
    {
        if (strpos($method, 'get') === 0) {
            $attribute = substr($method, 3);
            return property_exists($object, $attribute) ? $attribute : lcfirst($attribute);
        }
        return $method;
    }

    /**
     * Hydrate an object by populating getter/setter methods
     *
     * Hydrates an object by getter/setter methods of the object.
     *
     * {@inheritDoc}
     */
    public function hydrate(array $data, object $object)
    {
        $objectClass = get_class($object);

        foreach ($data as $property => $value) {
            $propertyFqn = $objectClass . '::$' . $property;

            if (! isset($this->hydrationMethodsCache[$propertyFqn])) {
                $setterName = 'set' . ucfirst($this->hydrateName($property, $data));

                $this->hydrationMethodsCache[$propertyFqn] = is_callable([$object, $setterName])
                    && (! $this->methodExistsCheck || method_exists($object, $setterName))
                    ? $setterName
                    : false;
            }

            if ($this->hydrationMethodsCache[$propertyFqn]) {
                $object->{$this->hydrationMethodsCache[$propertyFqn]}($this->hydrateValue($property, $value, $data));
            }
        }

        return $object;
    }

    /**
     * {@inheritDoc}
     */
    public function addFilter(string $name, $filter, int $condition = Filter\FilterComposite::CONDITION_OR) : void
    {
        $this->resetCaches();
        parent::addFilter($name, $filter, $condition);
    }

    /**
     * {@inheritDoc}
     */
    public function removeFilter(string $name) : void
    {
        $this->resetCaches();
        parent::removeFilter($name);
    }

    /**
     * {@inheritDoc}
     */
    public function setNamingStrategy(NamingStrategy\NamingStrategyInterface $strategy) : void
    {
        $this->resetCaches();
        parent::setNamingStrategy($strategy);
    }

    /**
     * {@inheritDoc}
     */
    public function removeNamingStrategy() : void
    {
        $this->resetCaches();
        parent::removeNamingStrategy();
    }

    /**
     * Reset all local hydration/extraction caches
     */
    private function resetCaches() : void
    {
        $this->hydrationMethodsCache = $this->extractionMethodsCache = [];
    }
}
