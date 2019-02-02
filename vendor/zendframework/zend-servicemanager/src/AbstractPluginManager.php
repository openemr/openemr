<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\ServiceManager;

use Interop\Container\ContainerInterface;
use Exception as BaseException;
use ReflectionMethod;

/**
 * ServiceManager implementation for managing plugins
 *
 * Automatically registers an initializer which should be used to verify that
 * a plugin instance is of a valid type. Additionally, allows plugins to accept
 * an array of options for the constructor, which can be used to configure
 * the plugin when retrieved. Finally, enables the allowOverride property by
 * default to allow registering factories, aliases, and invokables to take
 * the place of those provided by the implementing class.
 */
abstract class AbstractPluginManager extends ServiceManager implements ServiceLocatorAwareInterface
{
    /**
     * Allow overriding by default
     *
     * @var bool
     */
    protected $allowOverride = true;

    /**
     * Whether or not to auto-add a class as an invokable class if it exists
     *
     * @var bool
     */
    protected $autoAddInvokableClass = true;

    /**
     * Options to use when creating an instance
     *
     * @var mixed
     */
    protected $creationOptions = null;

    /**
     * The main service locator
     *
     * @var ServiceLocatorInterface
     */
    protected $serviceLocator;

    /**
     * Constructor
     *
     * Add a default initializer to ensure the plugin is valid after instance
     * creation.
     *
     * Additionally, the constructor provides forwards compatibility with v3 by
     * overloading the initial argument. v2 usage expects either null or a
     * ConfigInterface instance, and will ignore any other arguments. v3 expects
     * a ContainerInterface instance, and will use an array of configuration to
     * seed the current instance with services. In most cases, you can ignore the
     * constructor unless you are writing a specialized factory for your plugin
     * manager or overriding it.
     *
     * @param null|ConfigInterface|ContainerInterface $configOrContainerInstance
     * @param array $v3config If $configOrContainerInstance is a container, this
     *     value will be passed to the parent constructor.
     * @throws Exception\InvalidArgumentException if $configOrContainerInstance
     *     is neither null, nor a ConfigInterface, nor a ContainerInterface.
     */
    public function __construct($configOrContainerInstance = null, array $v3config = [])
    {
        if (null !== $configOrContainerInstance
            && ! $configOrContainerInstance instanceof ConfigInterface
            && ! $configOrContainerInstance instanceof ContainerInterface
        ) {
            throw new Exception\InvalidArgumentException(sprintf(
                '%s expects a ConfigInterface instance or ContainerInterface instance; received %s',
                get_class($this),
                (is_object($configOrContainerInstance)
                    ? get_class($configOrContainerInstance)
                    : gettype($configOrContainerInstance)
                )
            ));
        }

        if ($configOrContainerInstance instanceof ContainerInterface) {
            if (property_exists($this, 'serviceLocator')) {
                if (! empty($v3config)) {
                    parent::__construct(new Config($v3config));
                }
                $this->serviceLocator = $configOrContainerInstance;
            }

            if (property_exists($this, 'creationContext')) {
                if (! empty($v3config)) {
                    parent::__construct($v3config);
                }
                $this->creationContext = $configOrContainerInstance;
            }
        }

        if ($configOrContainerInstance instanceof ConfigInterface) {
            parent::__construct($configOrContainerInstance);
        }

        $this->addInitializer(function ($instance) {
            if ($instance instanceof ServiceLocatorAwareInterface) {
                $instance->setServiceLocator($this);
            }
        });
    }

    /**
     * Validate the plugin
     *
     * Checks that the filter loaded is either a valid callback or an instance
     * of FilterInterface.
     *
     * @param  mixed                      $plugin
     * @return void
     * @throws Exception\RuntimeException if invalid
     */
    abstract public function validatePlugin($plugin);

    /**
     * Retrieve a service from the manager by name
     *
     * Allows passing an array of options to use when creating the instance.
     * createFromInvokable() will use these and pass them to the instance
     * constructor if not null and a non-empty array.
     *
     * @param  string $name
     * @param  array  $options
     * @param  bool   $usePeeringServiceManagers
     *
     * @return object
     *
     * @throws Exception\ServiceNotFoundException
     * @throws Exception\ServiceNotCreatedException
     * @throws Exception\RuntimeException
     */
    public function get($name, $options = [], $usePeeringServiceManagers = true)
    {
        $isAutoInvokable = false;
        $cName = null;
        $sharedInstance = null;

        // Allow specifying a class name directly; registers as an invokable class
        if (!$this->has($name) && $this->autoAddInvokableClass && class_exists($name)) {
            $isAutoInvokable = true;

            $this->setInvokableClass($name, $name);
        }

        $this->creationOptions = $options;

        // If creation options were provided, we want to force creation of a
        // new instance.
        if (! empty($this->creationOptions)) {
            $cName = isset($this->canonicalNames[$name])
                ? $this->canonicalNames[$name]
                : $this->canonicalizeName($name);

            if (isset($this->instances[$cName])) {
                $sharedInstance = $this->instances[$cName];
                unset($this->instances[$cName]);
            }
        }

        try {
            $instance = parent::get($name, $usePeeringServiceManagers);
        } catch (Exception\ServiceNotFoundException $exception) {
            if ($sharedInstance) {
                $this->instances[$cName] = $sharedInstance;
            }
            $this->creationOptions = null;
            $this->tryThrowingServiceLocatorUsageException($name, $isAutoInvokable, $exception);
        } catch (Exception\ServiceNotCreatedException $exception) {
            if ($sharedInstance) {
                $this->instances[$cName] = $sharedInstance;
            }
            $this->creationOptions = null;
            $this->tryThrowingServiceLocatorUsageException($name, $isAutoInvokable, $exception);
        }

        $this->creationOptions = null;

        // If we had a previously shared instance, restore it.
        if ($sharedInstance) {
            $this->instances[$cName] = $sharedInstance;
        }

        try {
            $this->validatePlugin($instance);
        } catch (Exception\RuntimeException $exception) {
            $this->tryThrowingServiceLocatorUsageException($name, $isAutoInvokable, $exception);
        }

        // If we created a new instance using creation options, and it was
        // marked to share, we remove the shared instance
        // (options === cannot share)
        if ($cName
            && isset($this->instances[$cName])
            && $this->instances[$cName] === $instance
        ) {
            unset($this->instances[$cName]);
        }

        return $instance;
    }

    /**
     * Register a service with the locator.
     *
     * Validates that the service object via validatePlugin() prior to
     * attempting to register it.
     *
     * @param  string                                $name
     * @param  mixed                                 $service
     * @param  bool                                  $shared
     * @return AbstractPluginManager
     * @throws Exception\InvalidServiceNameException
     */
    public function setService($name, $service, $shared = true)
    {
        if ($service) {
            $this->validatePlugin($service);
        }
        parent::setService($name, $service, $shared);

        return $this;
    }

    /**
     * Set the main service locator so factories can have access to it to pull deps
     *
     * @param  ServiceLocatorInterface $serviceLocator
     * @return AbstractPluginManager
     */
    public function setServiceLocator(ServiceLocatorInterface $serviceLocator)
    {
        $this->serviceLocator = $serviceLocator;

        return $this;
    }

    /**
     * Get the main plugin manager. Useful for fetching dependencies from within factories.
     *
     * @return ServiceLocatorInterface
     */
    public function getServiceLocator()
    {
        return $this->serviceLocator;
    }

    /**
     * Attempt to create an instance via an invokable class
     *
     * Overrides parent implementation by passing $creationOptions to the
     * constructor, if non-null.
     *
     * @param  string                               $canonicalName
     * @param  string                               $requestedName
     * @return null|\stdClass
     * @throws Exception\ServiceNotCreatedException If resolved class does not exist
     */
    protected function createFromInvokable($canonicalName, $requestedName)
    {
        $invokable = $this->invokableClasses[$canonicalName];

        if (!class_exists($invokable)) {
            throw new Exception\ServiceNotFoundException(sprintf(
                '%s: failed retrieving "%s%s" via invokable class "%s"; class does not exist',
                get_class($this) . '::' . __FUNCTION__,
                $canonicalName,
                ($requestedName ? '(alias: ' . $requestedName . ')' : ''),
                $invokable
            ));
        }

        if (null === $this->creationOptions
            || (is_array($this->creationOptions) && empty($this->creationOptions))
        ) {
            $instance = new $invokable();
        } else {
            $instance = new $invokable($this->creationOptions);
        }

        return $instance;
    }

    /**
     * Attempt to create an instance via a factory class
     *
     * Overrides parent implementation by passing $creationOptions to the
     * constructor, if non-null.
     *
     * @param  string                               $canonicalName
     * @param  string                               $requestedName
     * @return mixed
     * @throws Exception\ServiceNotCreatedException If factory is not callable
     */
    protected function createFromFactory($canonicalName, $requestedName)
    {
        $factory            = $this->factories[$canonicalName];
        $hasCreationOptions = !(null === $this->creationOptions || (is_array($this->creationOptions) && empty($this->creationOptions)));

        if (is_string($factory) && class_exists($factory, true)) {
            if (!$hasCreationOptions) {
                $factory = new $factory();
            } else {
                $factory = new $factory($this->creationOptions);
            }

            $this->factories[$canonicalName] = $factory;
        }

        if ($factory instanceof FactoryInterface) {
            $instance = $this->createServiceViaCallback([$factory, 'createService'], $canonicalName, $requestedName);
        } elseif (is_callable($factory)) {
            $instance = $this->createServiceViaCallback($factory, $canonicalName, $requestedName);
        } else {
            throw new Exception\ServiceNotCreatedException(sprintf(
                'While attempting to create %s%s an invalid factory was registered for this instance type.',
                $canonicalName,
                ($requestedName ? '(alias: ' . $requestedName . ')' : '')
            ));
        }

        return $instance;
    }

    /**
     * Create service via callback
     *
     * @param  callable                                   $callable
     * @param  string                                     $cName
     * @param  string                                     $rName
     * @throws Exception\ServiceNotCreatedException
     * @throws Exception\ServiceNotFoundException
     * @throws Exception\CircularDependencyFoundException
     * @return object
     */
    protected function createServiceViaCallback($callable, $cName, $rName)
    {
        if (is_object($callable)) {
            $factory = $callable;
        } elseif (is_array($callable)) {
            // reset both rewinds and returns the value of the first array element
            $factory = reset($callable);
        } else {
            $factory = null;
        }

        if ($factory instanceof Factory\InvokableFactory) {
            // InvokableFactory::setCreationOptions has a different signature than
            // MutableCreationOptionsInterface; allows null value.
            $options = is_array($this->creationOptions) && ! empty($this->creationOptions)
                ? $this->creationOptions
                : null;
            $factory->setCreationOptions($options);
        } elseif ($factory instanceof MutableCreationOptionsInterface) {
            // MutableCreationOptionsInterface expects an array, always; pass an
            // empty array for lack of creation options.
            $options = is_array($this->creationOptions) && ! empty($this->creationOptions)
                ? $this->creationOptions
                : [];
            $factory->setCreationOptions($options);
        } elseif (isset($factory)
            && method_exists($factory, 'setCreationOptions')
        ) {
            // duck-type MutableCreationOptionsInterface for forward compatibility

            $options = $this->creationOptions;

            // If we have empty creation options, we have to find out if a default
            // value is present and use that; otherwise, we should use an empty
            // array, as that's the standard type-hint.
            if (! is_array($options) || empty($options)) {
                $r = new ReflectionMethod($factory, 'setCreationOptions');
                $params = $r->getParameters();
                $optionsParam = array_shift($params);
                $options = $optionsParam->isDefaultValueAvailable() ? $optionsParam->getDefaultValue() : [];
            }

            $factory->setCreationOptions($options);
        }

        return parent::createServiceViaCallback($callable, $cName, $rName);
    }

    /**
     * @param string        $serviceName
     * @param bool          $isAutoInvokable
     * @param BaseException $exception
     *
     * @throws BaseException
     * @throws Exception\ServiceLocatorUsageException
     */
    private function tryThrowingServiceLocatorUsageException(
        $serviceName,
        $isAutoInvokable,
        BaseException $exception
    ) {
        if ($isAutoInvokable) {
            $this->unregisterService($this->canonicalizeName($serviceName));
        }

        $serviceLocator = $this->getServiceLocator();

        if ($serviceLocator && $serviceLocator->has($serviceName)) {
            throw Exception\ServiceLocatorUsageException::fromInvalidPluginManagerRequestedServiceName(
                $this,
                $serviceLocator,
                $serviceName,
                $exception
            );
        }

        throw $exception;
    }
}
