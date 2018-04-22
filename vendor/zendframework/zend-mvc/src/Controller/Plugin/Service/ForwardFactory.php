<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Mvc\Controller\Plugin\Service;

use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Exception\ServiceNotCreatedException;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\Mvc\Controller\Plugin\Forward;

class ForwardFactory implements FactoryInterface
{
    /**
     * {@inheritDoc}
     *
     * @return Forward
     * @throws ServiceNotCreatedException if Controllermanager service is not found in application service locator
     */
    public function __invoke(ContainerInterface $container, $name, array $options = null)
    {
        if (! $container->has('ControllerManager')) {
            throw new ServiceNotCreatedException(sprintf(
                '%s requires that the application service manager contains a "%s" service; none found',
                __CLASS__,
                'ControllerManager'
            ));
        }
        $controllers = $container->get('ControllerManager');

        return new Forward($controllers);
    }

    /**
     * Create and return Forward instance
     *
     * For use with zend-servicemanager v2; proxies to __invoke().
     *
     * @param ServiceLocatorInterface $container
     * @return Forward
     */
    public function createService(ServiceLocatorInterface $container)
    {
        $parentContainer = $container->getServiceLocator() ?: $container;
        return $this($parentContainer, Forward::class);
    }
}
