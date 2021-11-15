<?php

/**
 * @see       https://github.com/laminas/laminas-view for the canonical source repository
 * @copyright https://github.com/laminas/laminas-view/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-view/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\View\Helper\Service;

use Interop\Container\ContainerInterface;
use Laminas\Authentication\AuthenticationService;
use Laminas\Authentication\AuthenticationServiceInterface;
use Laminas\ServiceManager\FactoryInterface;
use Laminas\ServiceManager\ServiceLocatorInterface;
use Laminas\View\Helper\Identity;

class IdentityFactory implements FactoryInterface
{
    /**
     * {@inheritDoc}
     *
     * @param ContainerInterface $container
     * @param string $name
     * @param null|array $options
     * @return \Laminas\View\Helper\Identity
     */
    public function __invoke(ContainerInterface $container, $name, array $options = null)
    {
        // test if we are using Laminas\ServiceManager v2 or v3
        if (! method_exists($container, 'configure')) {
            $container = $container->getServiceLocator();
        }

        $helper = new Identity();

        if (null !== ($authenticationService = $this->discoverAuthenticationService($container))) {
            $helper->setAuthenticationService($authenticationService);
        }

        return $helper;
    }

    /**
     * Create service
     *
     * @param ServiceLocatorInterface $serviceLocator
     * @return mixed
     */
    public function createService(ServiceLocatorInterface $serviceLocator, $rName = null, $cName = null)
    {
        return $this($serviceLocator, $cName);
    }

    /**
     * @return null|AuthenticationServiceInterface
     */
    private function discoverAuthenticationService(ContainerInterface $container)
    {
        if ($container->has(AuthenticationService::class)) {
            return $container->get(AuthenticationService::class);
        }

        if ($container->has(\Zend\Authentication\AuthenticationService::class)) {
            return $container->get(\Zend\Authentication\AuthenticationService::class);
        }

        return $container->has(AuthenticationServiceInterface::class)
            ? $container->get(AuthenticationServiceInterface::class)
            : ($container->has(\Zend\Authentication\AuthenticationServiceInterface::class)
                ? $container->get(\Zend\Authentication\AuthenticationServiceInterface::class)
                : null);
    }
}
