<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Hydrator;

use Traversable;
use Zend\Stdlib\ArrayUtils;

class ClassMethods extends AbstractHydrator implements HydratorOptionsInterface
{
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
     * @var string[][]
     */
    private $extractionMethodsCache = [];

    /**
     * Flag defining whether array keys are underscore-separated (true) or camel case (false)
     *
     * @var bool
     */
    protected $underscoreSeparatedKeys = true;

    /**
     * @var Filter\FilterInterface
     */
    private $callableMethodFilter;

    /**
     * Define if extract values will use camel case or name with underscore
     * @param bool|array $underscoreSeparatedKeys
     */
    public function __construct($underscoreSeparatedKeys = true)
    {
        parent::__construct();
        $this->setUnderscoreSeparatedKeys($underscoreSeparatedKeys);

        $this->callableMethodFilter = new Filter\OptionalParametersFilter();

        $this->filterComposite->addFilter('is', new Filter\IsFilter());
        $this->filterComposite->addFilter('has', new Filter\HasFilter());
        $this->filterComposite->addFilter('get', new Filter\GetFilter());
        $this->filterComposite->addFilter(
            'parameter',
            new Filter\OptionalParametersFilter(),
            Filter\FilterComposite::CONDITION_AND
        );
    }

    /**
     * @param  array|Traversable                 $options
     * @return ClassMethods
     * @throws Exception\InvalidArgumentException
     */
    public function setOptions($options)
    {
        if ($options instanceof Traversable) {
            $options = ArrayUtils::iteratorToArray($options);
        } elseif (!is_array($options)) {
            throw new Exception\InvalidArgumentException(
                'The options parameter must be an array or a Traversable'
            );
        }
        if (isset($options['underscoreSeparatedKeys'])) {
            $this->setUnderscoreSeparatedKeys($options['underscoreSeparatedKeys']);
        }

        return $this;
    }

    /**
     * @param  bool      $underscoreSeparatedKeys
     * @return ClassMethods
     */
    public function setUnderscoreSeparatedKeys($underscoreSeparatedKeys)
    {
        $this->underscoreSeparatedKeys = (bool) $underscoreSeparatedKeys;

        if ($this->underscoreSeparatedKeys) {
            $this->setNamingStrategy(new NamingStrategy\UnderscoreNamingStrategy);
        } elseif ($this->getNamingStrategy() instanceof NamingStrategy\UnderscoreNamingStrategy) {
            $this->removeNamingStrategy();
        }

        return $this;
    }

    /**
     * @return bool
     */
    public function getUnderscoreSeparatedKeys()
    {
        return $this->underscoreSeparatedKeys;
    }

    /**
     * Extract values from an object with class methods
     *
     * Extracts the getter/setter of the given $object.
     *
     * @param  object                           $object
     * @return array
     * @throws Exception\BadMethodCallException for a non-object $object
     */
    public function extract($object)
    {
        if (!is_object($object)) {
            throw new Exception\BadMethodCallException(sprintf(
                '%s expects the provided $object to be a PHP object)',
                __METHOD__
            ));
        }

        $objectClass = get_class($object);

        // reset the hydrator's hydrator's cache for this object, as the filter may be per-instance
        if ($object instanceof Filter\FilterProviderInterface) {
            $this->extractionMethodsCache[$objectClass] = null;
        }

        // pass 1 - finding out which properties can be extracted, with which methods (populate hydration cache)
        if (! isset($this->extractionMethodsCache[$objectClass])) {
            $this->extractionMethodsCache[$objectClass] = [];
            $filter                                     = $this->filterComposite;
            $methods                                    = get_class_methods($object);

            if ($object instanceof Filter\FilterProviderInterface) {
                $filter = new Filter\FilterComposite(
                    [$object->getFilter()],
                    [new Filter\MethodMatchFilter('getFilter')]
                );
            }

            foreach ($methods as $method) {
                $methodFqn = $objectClass . '::' . $method;

                if (! ($filter->filter($methodFqn) && $this->callableMethodFilter->filter($methodFqn))) {
                    continue;
                }

                $attribute = $method;

                if (strpos($method, 'get') === 0) {
                    $attribute = substr($method, 3);
                    if (!property_exists($object, $attribute)) {
                        $attribute = lcfirst($attribute);
                    }
                }

                $this->extractionMethodsCache[$objectClass][$method] = $attribute;
            }
        }

        $values = [];

        // pass 2 - actually extract data
        foreach ($this->extractionMethodsCache[$objectClass] as $methodName => $attributeName) {
            $realAttributeName          = $this->extractName($attributeName, $object);
            $values[$realAttributeName] = $this->extractValue($realAttributeName, $object->$methodName(), $object);
        }

        return $values;
    }

    /**
     * Hydrate an object by populating getter/setter methods
     *
     * Hydrates an object by getter/setter methods of the object.
     *
     * @param  array                            $data
     * @param  object                           $object
     * @return object
     * @throws Exception\BadMethodCallException for a non-object $object
     */
    public function hydrate(array $data, $object)
    {
        if (!is_object($object)) {
            throw new Exception\BadMethodCallException(sprintf(
                '%s expects the provided $object to be a PHP object)',
                __METHOD__
            ));
        }

        $objectClass = get_class($object);

        foreach ($data as $property => $value) {
            $propertyFqn = $objectClass . '::$' . $property;

            if (! isset($this->hydrationMethodsCache[$propertyFqn])) {
                $setterName = 'set' . ucfirst($this->hydrateName($property, $data));

                $this->hydrationMethodsCache[$propertyFqn] = is_callable([$object, $setterName])
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
    public function addFilter($name, $filter, $condition = Filter\FilterComposite::CONDITION_OR)
    {
        $this->resetCaches();

        return parent::addFilter($name, $filter, $condition);
    }

    /**
     * {@inheritDoc}
     */
    public function removeFilter($name)
    {
        $this->resetCaches();

        return parent::removeFilter($name);
    }

    /**
     * {@inheritDoc}
     */
    public function setNamingStrategy(NamingStrategy\NamingStrategyInterface $strategy)
    {
        $this->resetCaches();

        return parent::setNamingStrategy($strategy);
    }

    /**
     * {@inheritDoc}
     */
    public function removeNamingStrategy()
    {
        $this->resetCaches();

        return parent::removeNamingStrategy();
    }

    /**
     * Reset all local hydration/extraction caches
     */
    private function resetCaches()
    {
        $this->hydrationMethodsCache = $this->extractionMethodsCache = [];
    }
}
