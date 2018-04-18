<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\InputFilter;

use Interop\Container\ContainerInterface;
use Zend\Filter\FilterPluginManager;
use Zend\ServiceManager\AbstractFactoryInterface;
use Zend\ServiceManager\AbstractPluginManager;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\Validator\ValidatorPluginManager;

class InputFilterAbstractServiceFactory implements AbstractFactoryInterface
{
    /**
     * @var Factory
     */
    protected $factory;

    /**
     * @param ContainerInterface      $services
     * @param string                  $rName
     * @param array                   $options
     * @return InputFilterInterface
     */
    public function __invoke(ContainerInterface $services, $rName, array  $options = null)
    {
        $allConfig = $services->get('config');
        $config    = $allConfig['input_filter_specs'][$rName];
        $factory   = $this->getInputFilterFactory($services);

        return $factory->createInputFilter($config);
    }

    /**
     *
     * @param ContainerInterface $services
     * @param string $rName
     * @return bool
     */
    public function canCreate(ContainerInterface $services, $rName)
    {
        if (! $services->has('config')) {
            return false;
        }

        $config = $services->get('config');
        if (! isset($config['input_filter_specs'][$rName])
            || ! is_array($config['input_filter_specs'][$rName])
        ) {
            return false;
        }

        return true;
    }

    /**
     * Determine if we can create a service with name (v2)
     *
     * @param ServiceLocatorInterface $container
     * @param $name
     * @param $requestedName
     * @return bool
     */
    public function canCreateServiceWithName(ServiceLocatorInterface $container, $name, $requestedName)
    {
        // v2 => may need to get parent service locator
        if ($container instanceof AbstractPluginManager) {
            $container = $container->getServiceLocator() ?: $container;
        }

        return $this->canCreate($container, $requestedName);
    }

    /**
     * Create the requested service (v2)
     *
     * @param ServiceLocatorInterface $container
     * @param string                  $cName
     * @param string                  $rName
     * @return InputFilterInterface
     */
    public function createServiceWithName(ServiceLocatorInterface $container, $cName, $rName)
    {
        // v2 => may need to get parent service locator
        if ($container instanceof AbstractPluginManager) {
            $container = $container->getServiceLocator() ?: $container;
        }

        return $this($container, $rName);
    }

    /**
     * @param ContainerInterface $container
     * @return Factory
     */
    protected function getInputFilterFactory(ContainerInterface $container)
    {
        if ($this->factory instanceof Factory) {
            return $this->factory;
        }

        $this->factory = new Factory();
        $this->factory
            ->getDefaultFilterChain()
            ->setPluginManager($this->getFilterPluginManager($container));
        $this->factory
            ->getDefaultValidatorChain()
            ->setPluginManager($this->getValidatorPluginManager($container));

        $this->factory->setInputFilterManager($container->get('InputFilterManager'));

        return $this->factory;
    }

    /**
     * @param ContainerInterface $container
     * @return FilterPluginManager
     */
    protected function getFilterPluginManager(ContainerInterface $container)
    {
        if ($container->has('FilterManager')) {
            return $container->get('FilterManager');
        }

        return new FilterPluginManager($container);
    }

    /**
     * @param ContainerInterface $container
     * @return ValidatorPluginManager
     */
    protected function getValidatorPluginManager(ContainerInterface $container)
    {
        if ($container->has('ValidatorManager')) {
            return $container->get('ValidatorManager');
        }

        return new ValidatorPluginManager($container);
    }
}
