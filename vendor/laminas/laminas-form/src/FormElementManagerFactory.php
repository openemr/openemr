<?php

/**
 * @see       https://github.com/laminas/laminas-form for the canonical source repository
 * @copyright https://github.com/laminas/laminas-form/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-form/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\Form;

use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\AbstractPluginManager;
use Laminas\ServiceManager\Config;
use Laminas\ServiceManager\FactoryInterface;
use Laminas\ServiceManager\ServiceLocatorInterface;

use function is_array;
use function method_exists;

class FormElementManagerFactory implements FactoryInterface
{
    /**
     * laminas-servicemanager v2 support for invocation options.
     *
     * @param array
     *
     * @deprecated Call \Laminas\Form\FormElementManagerFactory::__invoke with 3rd parameter as options instead
     */
    protected $creationOptions;

    /**
     * {@inheritDoc}
     *
     * @return AbstractPluginManager
     */
    public function __invoke(ContainerInterface $container, $name, array $options = null)
    {
        $pluginManager = new \Laminas\Form\FormElementManager($container, $options ?: []);

        // If this is in a laminas-mvc application, the ServiceListener will inject
        // merged configuration during bootstrap.
        if ($container->has('ServiceListener')) {
            return $pluginManager;
        }

        // If we do not have a config service, nothing more to do
        if (! $container->has('config')) {
            return $pluginManager;
        }

        $config = $container->get('config');

        // If we do not have form_elements configuration, nothing more to do
        if (! isset($config['form_elements']) || ! is_array($config['form_elements'])) {
            return $pluginManager;
        }

        // Wire service configuration for forms and elements
        (new Config($config['form_elements']))->configureServiceManager($pluginManager);

        return $pluginManager;
    }

    /**
     * {@inheritDoc}
     *
     * @return AbstractPluginManager
     */
    public function createService(ServiceLocatorInterface $container, $name = null, $requestedName = null)
    {
        return $this(
            $container,
            $requestedName ?: __NAMESPACE__ . '\FormElementManager',
            $this->creationOptions
        );
    }

    /**
     * laminas-servicemanager v2 support for invocation options.
     *
     * @param array $options
     * @return void
     *
     * @deprecated Call \Laminas\Form\FormElementManagerFactory::__invoke with 3rd parameter as options instead
     */
    public function setCreationOptions(array $options)
    {
        $this->creationOptions = $options;
    }
}
