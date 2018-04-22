<?php
/**
 * @link      http://github.com/zendframework/zend-form for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Form\FormElementManager;

use Zend\Form\Exception;

/**
 * Trait providing common logic between FormElementManager implementations.
 *
 * Trait does not define properties, as the properties common between the
 * two versions are originally defined in their parent class, causing a
 * resolution conflict.
 */
trait FormElementManagerTrait
{
    /**
     * Retrieve a service from the manager by name
     *
     * Allows passing an array of options to use when creating the instance.
     * createFromInvokable() will use these and pass them to the instance
     * constructor if not null and a non-empty array.
     *
     * @param  string $name
     * @param  string|array $options
     * @param  bool $usePeeringServiceManagers
     * @return object
     */
    public function get($name, $options = [], $usePeeringServiceManagers = true)
    {
        if (is_string($options)) {
            $options = ['name' => $options];
        }

        if (! $this->has($name)) {
            if (! $this->autoAddInvokableClass || ! class_exists($name)) {
                throw new Exception\InvalidElementException(sprintf(
                    'A plugin by the name "%s" was not found in the plugin manager %s',
                    $name,
                    get_class($this)
                ));
            }

            $this->setInvokableClass($name, $name);
        }
        return parent::get($name, $options, $usePeeringServiceManagers);
    }

    /**
     * Try to pull hydrator from the creation context, or instantiates it from its name
     *
     * @param  string $hydratorName
     * @return mixed
     * @throws Exception\DomainException
     */
    public function getHydratorFromName($hydratorName)
    {
        $services = isset($this->creationContext)
            ? $this->creationContext // v3
            : $this->serviceLocator; // v2

        if ($services && $services->has('HydratorManager')) {
            $hydrators = $services->get('HydratorManager');
            if ($hydrators->has($hydratorName)) {
                return $hydrators->get($hydratorName);
            }
        }

        if ($services && $services->has($hydratorName)) {
            return $services->get($hydratorName);
        }

        if (! class_exists($hydratorName)) {
            throw new Exception\DomainException(sprintf(
                'Expects string hydrator name to be a valid class name; received "%s"',
                $hydratorName
            ));
        }

        $hydrator = new $hydratorName;
        return $hydrator;
    }

    /**
     * Try to pull factory from the creation context, or instantiates it from its name
     *
     * @param  string $factoryName
     * @return mixed
     * @throws Exception\DomainException
     */
    public function getFactoryFromName($factoryName)
    {
        $services = isset($this->creationContext)
            ? $this->creationContext // v3
            : $this->serviceLocator; // v2

        if ($services && $services->has($factoryName)) {
            return $services->get($factoryName);
        }

        if (! class_exists($factoryName)) {
            throw new Exception\DomainException(sprintf(
                'Expects string factory name to be a valid class name; received "%s"',
                $factoryName
            ));
        }

        $factory = new $factoryName;
        return $factory;
    }
}
