<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Mvc\Controller;

use Interop\Container\ContainerInterface;
use Zend\EventManager\EventManagerAwareInterface;
use Zend\EventManager\SharedEventManagerInterface;
use Zend\Mvc\Exception;
use Zend\ServiceManager\AbstractPluginManager;
use Zend\ServiceManager\ConfigInterface;
use Zend\ServiceManager\Exception\InvalidServiceException;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\Stdlib\DispatchableInterface;

/**
 * Manager for loading controllers
 *
 * Does not define any controllers by default, but does add a validator.
 */
class ControllerManager extends AbstractPluginManager
{
    /**
     * We do not want arbitrary classes instantiated as controllers.
     *
     * @var bool
     */
    protected $autoAddInvokableClass = false;

    /**
     * Controllers must be of this type.
     *
     * @var string
     */
    protected $instanceOf = DispatchableInterface::class;

    /**
     * Constructor
     *
     * Injects an initializer for injecting controllers with an
     * event manager and plugin manager.
     *
     * @param  ConfigInterface|ContainerInterface $container
     * @param  array $v3config
     */
    public function __construct($configOrContainerInstance, array $v3config = [])
    {
        $this->addInitializer([$this, 'injectEventManager']);
        $this->addInitializer([$this, 'injectConsole']);
        $this->addInitializer([$this, 'injectPluginManager']);
        parent::__construct($configOrContainerInstance, $v3config);

        // Added after parent construction, as v2 abstract plugin managers add
        // one during construction.
        $this->addInitializer([$this, 'injectServiceLocator']);
    }

    /**
     * Validate a plugin (v3)
     *
     * {@inheritDoc}
     */
    public function validate($plugin)
    {
        if (! $plugin instanceof $this->instanceOf) {
            throw new InvalidServiceException(sprintf(
                'Plugin of type "%s" is invalid; must implement %s',
                (is_object($plugin) ? get_class($plugin) : gettype($plugin)),
                $this->instanceOf
            ));
        }
    }

    /**
     * Validate a plugin (v2)
     *
     * {@inheritDoc}
     *
     * @throws Exception\InvalidControllerException
     */
    public function validatePlugin($plugin)
    {
        try {
            $this->validate($plugin);
        } catch (InvalidServiceException $e) {
            throw new Exception\InvalidControllerException(
                $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Initializer: inject EventManager instance
     *
     * If we have an event manager composed already, make sure it gets injected
     * with the shared event manager.
     *
     * The AbstractController lazy-instantiates an EM instance, which is why
     * the shared EM injection needs to happen; the conditional will always
     * pass.
     *
     * @param ContainerInterface|DispatchableInterface $first Container when
     *     using zend-servicemanager v3; controller under v2.
     * @param DispatchableInterface|ContainerInterface $second Controller when
     *     using zend-servicemanager v3; container under v2.
     */
    public function injectEventManager($first, $second)
    {
        if ($first instanceof ContainerInterface) {
            $container = $first;
            $controller = $second;
        } else {
            $container = $second;
            $controller = $first;
        }

        if (! $controller instanceof EventManagerAwareInterface) {
            return;
        }

        $events = $controller->getEventManager();
        if (! $events || ! $events->getSharedManager() instanceof SharedEventManagerInterface) {
            // For v2, we need to pull the parent service locator
            if (! method_exists($container, 'configure')) {
                $container = $container->getServiceLocator() ?: $container;
            }

            $controller->setEventManager($container->get('EventManager'));
        }
    }

    /**
     * Initializer: inject Console adapter instance
     *
     * @param ContainerInterface|DispatchableInterface $first Container when
     *     using zend-servicemanager v3; controller under v2.
     * @param DispatchableInterface|ContainerInterface $second Controller when
     *     using zend-servicemanager v3; container under v2.
     */
    public function injectConsole($first, $second)
    {
        if ($first instanceof ContainerInterface) {
            $container = $first;
            $controller = $second;
        } else {
            $container = $second;
            $controller = $first;
        }

        if (! $controller instanceof AbstractConsoleController) {
            return;
        }

        // For v2, we need to pull the parent service locator
        if (! method_exists($container, 'configure')) {
            $container = $container->getServiceLocator() ?: $container;
        }

        $controller->setConsole($container->get('Console'));
    }

    /**
     * Initializer: inject plugin manager
     *
     * @param ContainerInterface|DispatchableInterface $first Container when
     *     using zend-servicemanager v3; controller under v2.
     * @param DispatchableInterface|ContainerInterface $second Controller when
     *     using zend-servicemanager v3; container under v2.
     */
    public function injectPluginManager($first, $second)
    {
        if ($first instanceof ContainerInterface) {
            $container = $first;
            $controller = $second;
        } else {
            $container = $second;
            $controller = $first;
        }

        if (! method_exists($controller, 'setPluginManager')) {
            return;
        }

        // For v2, we need to pull the parent service locator
        if (! method_exists($container, 'configure')) {
            $container = $container->getServiceLocator() ?: $container;
        }

        $controller->setPluginManager($container->get('ControllerPluginManager'));
    }

    /**
     * Initializer: inject service locator
     *
     * @param ContainerInterface|DispatchableInterface $first Container when
     *     using zend-servicemanager v3; controller under v2.
     * @param DispatchableInterface|ContainerInterface $second Controller when
     *     using zend-servicemanager v3; container under v2.
     */
    public function injectServiceLocator($first, $second)
    {
        if ($first instanceof ContainerInterface) {
            $container = $first;
            $controller = $second;
        } else {
            $container = $second;
            $controller = $first;
        }

        // For v2, we need to pull the parent service locator
        if (! method_exists($container, 'configure')) {
            $container = $container->getServiceLocator() ?: $container;
        }

        // Inject AbstractController extensions that are not ServiceLocatorAware
        // with the service manager, but do not emit a deprecation notice. We'll
        // emit it from AbstractController::getServiceLocator() instead.
        if (! $controller instanceof ServiceLocatorAwareInterface
            && $controller instanceof AbstractController
            && method_exists($controller, 'setServiceLocator')
        ) {
            // Do not emit deprecation notice in this case
            $controller->setServiceLocator($container);
        }

        // If a controller implements ServiceLocatorAwareInterface explicitly, we
        // inject, but emit a deprecation notice. Since AbstractController no longer
        // explicitly does this, this will only affect userland controllers.
        if ($controller instanceof ServiceLocatorAwareInterface) {
            trigger_error(sprintf(
                'ServiceLocatorAwareInterface is deprecated and will be removed in version 3.0, along '
                . 'with the ServiceLocatorAwareInitializer. Please update your class %s to remove '
                . 'the implementation, and start injecting your dependencies via factory instead.',
                get_class($controller)
            ), E_USER_DEPRECATED);
            $controller->setServiceLocator($container);
        }
    }
}
