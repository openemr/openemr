<?php
/**
 * @see       https://github.com/zendframework/zend-view for the canonical source repository
 * @copyright Copyright (c) 2017 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   https://github.com/zendframework/zend-view/blob/master/LICENSE.md New BSD License
 */

namespace Zend\View\Helper\Service;

use Interop\Container\ContainerInterface;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\View\Exception;
use Zend\View\Helper\Asset;

class AssetFactory implements FactoryInterface
{
    /**
     * {@inheritDoc}
     *
     * @param ContainerInterface $container
     * @param string $name
     * @param null|array $options
     * @return Asset
     * @throws Exception\RuntimeException
     */
    public function __invoke(ContainerInterface $container, $name, array $options = null)
    {
        // test if we are using Zend\ServiceManager v2 or v3
        if (! method_exists($container, 'configure')) {
            $container = $container->getServiceLocator();
        }
        $helper = new Asset();

        $config = $container->get('config');
        if (isset($config['view_helper_config']['asset'])) {
            $configHelper = $config['view_helper_config']['asset'];
            if (isset($configHelper['resource_map']) && is_array($configHelper['resource_map'])) {
                $helper->setResourceMap($configHelper['resource_map']);
            } else {
                throw new Exception\RuntimeException('Invalid resource map configuration.');
            }
        }

        return $helper;
    }

    /**
     * Create service
     *
     * @param ServiceLocatorInterface $serviceLocator
     * @param string|null $rName
     * @param string|null $cName
     * @return Asset
     */
    public function createService(ServiceLocatorInterface $serviceLocator, $rName = null, $cName = null)
    {
        return $this($serviceLocator, $cName);
    }
}
