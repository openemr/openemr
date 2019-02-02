<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Hydrator;

use Zend\ServiceManager\AbstractPluginManager;
use Zend\ServiceManager\Exception\InvalidServiceException;
use Zend\ServiceManager\Factory\InvokableFactory;

/**
 * Plugin manager implementation for hydrators.
 *
 * Enforces that adapters retrieved are instances of HydratorInterface
 */
class HydratorPluginManager extends AbstractPluginManager
{
    /**
     * Default aliases
     *
     * @var array
     */
    protected $aliases = [
        'arrayserializable'  => ArraySerializable::class,
        'arraySerializable'  => ArraySerializable::class,
        'ArraySerializable'  => ArraySerializable::class,
        'classmethods'       => ClassMethods::class,
        'classMethods'       => ClassMethods::class,
        'ClassMethods'       => ClassMethods::class,
        'delegatinghydrator' => DelegatingHydrator::class,
        'delegatingHydrator' => DelegatingHydrator::class,
        'DelegatingHydrator' => DelegatingHydrator::class,
        'objectproperty'     => ObjectProperty::class,
        'objectProperty'     => ObjectProperty::class,
        'ObjectProperty'     => ObjectProperty::class,
        'reflection'         => Reflection::class,
        'Reflection'         => Reflection::class,
    ];

    /**
     * Default factory-based adapters
     *
     * @var array
     */
    protected $factories = [
        ArraySerializable::class                => InvokableFactory::class,
        ClassMethods::class                     => InvokableFactory::class,
        DelegatingHydrator::class               => DelegatingHydratorFactory::class,
        ObjectProperty::class                   => InvokableFactory::class,
        Reflection::class                       => InvokableFactory::class,

        // v2 normalized FQCNs
        'zendhydratorarrayserializable'         => InvokableFactory::class,
        'zendhydratorclassmethods'              => InvokableFactory::class,
        'zendhydratordelegatinghydrator'        => DelegatingHydratorFactory::class,
        'zendhydratorobjectproperty'            => InvokableFactory::class,
        'zendhydratorreflection'                => InvokableFactory::class,
    ];

    /**
     * Whether or not to share by default (v3)
     *
     * @var bool
     */
    protected $sharedByDefault = false;

    /**
     * Whether or not to share by default (v2)
     *
     * @var bool
     */
    protected $shareByDefault = false;

    /**
     * {inheritDoc}
     */
    protected $instanceOf = HydratorInterface::class;

    /**
     * Validate the plugin is of the expected type (v3).
     *
     * Checks that the filter loaded is either a valid callback or an instance
     * of FilterInterface.
     *
     * @param mixed $instance
     * @throws InvalidServiceException
     */
    public function validate($instance)
    {
        if ($instance instanceof $this->instanceOf) {
            // we're okay
            return;
        }

        throw new InvalidServiceException(sprintf(
            'Plugin of type %s is invalid; must implement Zend\Hydrator\HydratorInterface',
            (is_object($instance) ? get_class($instance) : gettype($instance))
        ));
    }

    /**
     * {@inheritDoc} (v2)
     */
    public function validatePlugin($plugin)
    {
        try {
            $this->validate($plugin);
        } catch (InvalidServiceException $e) {
            throw new Exception\RuntimeException($e->getMessage(), $e->getCode(), $e);
        }
    }
}
