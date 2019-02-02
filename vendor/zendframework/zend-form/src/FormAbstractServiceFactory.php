<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Form;

use Interop\Container\ContainerInterface;
use Zend\InputFilter\InputFilterInterface;
use Zend\ServiceManager\AbstractFactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class FormAbstractServiceFactory implements AbstractFactoryInterface
{
    /**
     * @var array
     */
    protected $config;

    /**
     * @var string Top-level configuration key indicating forms configuration
     */
    protected $configKey     = 'forms';

    /**
     * @var Factory Form factory used to create forms
     */
    protected $factory;

    /**
     * Create a form (v3)
     *
     * @param ContainerInterface $container
     * @param string $requestedName
     * @param array|null $options
     * @return ElementInterface
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $config  = $this->getConfig($container);
        $config  = $config[$requestedName];
        $factory = $this->getFormFactory($container);

        $this->marshalInputFilter($config, $container, $factory);
        return $factory->createForm($config);
    }

    /**
     * Can we create the requested service? (v3)
     *
     * @param ContainerInterface $container
     * @param string $requestedName
     * @return bool
     */
    public function canCreate(ContainerInterface $container, $requestedName)
    {
        // avoid infinite loops when looking up config
        if ($requestedName == 'config') {
            return false;
        }

        $config = $this->getConfig($container);
        if (empty($config)) {
            return false;
        }

        return (isset($config[$requestedName])
            && is_array($config[$requestedName])
            && ! empty($config[$requestedName]));
    }

    /**
     * Can we create the requested service? (v2)
     *
     * @param  ServiceLocatorInterface $serviceLocator
     * @param  string $name Service name (as resolved by ServiceManager)
     * @param  string $requestedName Name by which service was requested
     * @return bool
     */
    public function canCreateServiceWithName(ServiceLocatorInterface $serviceLocator, $name, $requestedName)
    {
        return $this->canCreate($serviceLocator, $requestedName);
    }

    /**
     * Create a form (v2)
     *
     * @param  ServiceLocatorInterface $serviceLocator
     * @param  string $name Service name (as resolved by ServiceManager)
     * @param  string $requestedName Name by which service was requested
     * @return Form
     */
    public function createServiceWithName(ServiceLocatorInterface $serviceLocator, $name, $requestedName)
    {
        return $this($serviceLocator, $requestedName);
    }

    /**
     * Get forms configuration, if any
     *
     * @param  ServiceLocatorInterface $container
     * @return array
     */
    protected function getConfig(ContainerInterface $container)
    {
        if ($this->config !== null) {
            return $this->config;
        }

        if (! $container->has('config')) {
            $this->config = [];
            return $this->config;
        }

        $config = $container->get('config');
        if (! isset($config[$this->configKey])
            || ! is_array($config[$this->configKey])
        ) {
            $this->config = [];
            return $this->config;
        }

        $this->config = $config[$this->configKey];
        return $this->config;
    }

    /**
     * Retrieve the form factory, creating it if necessary
     *
     * @param  ContainerInterface $services
     * @return Factory
     */
    protected function getFormFactory(ContainerInterface $container)
    {
        if ($this->factory instanceof Factory) {
            return $this->factory;
        }

        $elements = null;
        if ($container->has('FormElementManager')) {
            $elements = $container->get('FormElementManager');
        }

        $this->factory = new Factory($elements);
        return $this->factory;
    }

    /**
     * Marshal the input filter into the configuration
     *
     * If an input filter is specified:
     * - if the InputFilterManager is present, checks if it's there; if so,
     *   retrieves it and resets the specification to the instance.
     * - otherwise, pulls the input filter factory from the form factory, and
     *   attaches the FilterManager and ValidatorManager to it.
     *
     * @param array $config
     * @param ContainerInterface $container
     * @param Factory $formFactory
     */
    protected function marshalInputFilter(array &$config, ContainerInterface $container, Factory $formFactory)
    {
        if (! isset($config['input_filter'])) {
            return;
        }

        if ($config['input_filter'] instanceof InputFilterInterface) {
            return;
        }

        if (is_string($config['input_filter'])
            && $container->has('InputFilterManager')
        ) {
            $inputFilters = $container->get('InputFilterManager');
            if ($inputFilters->has($config['input_filter'])) {
                $config['input_filter'] = $inputFilters->get($config['input_filter']);
                return;
            }
        }

        $inputFilterFactory = $formFactory->getInputFilterFactory();
        $inputFilterFactory->getDefaultFilterChain()->setPluginManager($container->get('FilterManager'));
        $inputFilterFactory->getDefaultValidatorChain()->setPluginManager($container->get('ValidatorManager'));
    }
}
