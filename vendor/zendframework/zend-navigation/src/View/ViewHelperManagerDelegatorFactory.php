<?php
/**
 * @link      http://github.com/zendframework/zend-navigation for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Navigation\View;

use Interop\Container\ContainerInterface;
use Zend\ServiceManager\DelegatorFactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Inject the zend-view HelperManager with zend-navigation view helper configuration.
 *
 * This approach is used for backwards compatibility. The HelperConfig class performs
 * work to ensure that the navigation helper and all its sub-helpers are injected
 * with the view helper manager and application container.
 */
class ViewHelperManagerDelegatorFactory implements DelegatorFactoryInterface
{
    /**
     * {@inheritDoc}
     *
     * @return \Zend\View\HelperPluginManager
     */
    public function __invoke(ContainerInterface $container, $name, callable $callback, array $options = null)
    {
        $viewHelpers = $callback();
        (new HelperConfig())->configureServiceManager($viewHelpers);
        return $viewHelpers;
    }

    /**
     * {@inheritDoc}
     *
     * @return \Zend\View\HelperPluginManager
     */
    public function createDelegatorWithName(ServiceLocatorInterface $container, $name, $requestedName, $callback)
    {
        return $this($container, $requestedName, $callback);
    }
}
