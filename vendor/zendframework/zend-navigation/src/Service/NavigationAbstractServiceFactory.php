<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Navigation\Service;

use Interop\Container\ContainerInterface;
use Zend\Navigation\Navigation;
use Zend\ServiceManager\AbstractFactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Navigation abstract service factory
 *
 * Allows configuring several navigation instances. If you have a navigation config key named "special" then you can
 * use $container->get('Zend\Navigation\Special') to retrieve a navigation instance with this configuration.
 */
final class NavigationAbstractServiceFactory implements AbstractFactoryInterface
{
    /**
     * Top-level configuration key indicating navigation configuration
     *
     * @var string
     */
    const CONFIG_KEY = 'navigation';

    /**
     * Service manager factory prefix
     *
     * @var string
     */
    const SERVICE_PREFIX = 'Zend\\Navigation\\';

    /**
     * Navigation configuration
     *
     * @var array
     */
    protected $config;

    /**
     * Can we create a navigation by the requested name? (v3)
     *
     * @param ContainerInterface $container
     * @param string $requestedName Name by which service was requested, must
     *     start with Zend\Navigation\
     * @return bool
     */
    public function canCreate(ContainerInterface $container, $requestedName)
    {
        if (0 !== strpos($requestedName, self::SERVICE_PREFIX)) {
            return false;
        }
        $config = $this->getConfig($container);

        return $this->hasNamedConfig($requestedName, $config);
    }

    /**
     * Can we create a navigation by the requested name? (v2)
     *
     * @param ServiceLocatorInterface $container
     * @param string $name Normalized name by which service was requested;
     *     ignored.
     * @param string $requestedName Name by which service was requested, must
     *     start with Zend\Navigation\
     * @return bool
     */
    public function canCreateServiceWithName(ServiceLocatorInterface $container, $name, $requestedName)
    {
        return $this->canCreate($container, $requestedName);
    }

    /**
     * {@inheritDoc}
     *
     * @return Navigation
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $config  = $this->getConfig($container);
        $factory = new ConstructedNavigationFactory($this->getNamedConfig($requestedName, $config));
        return $factory($container, $requestedName);
    }

    /**
     * Can we create a navigation by the requested name? (v2)
     *
     * @param ServiceLocatorInterface $container
     * @param string $name Normalized name by which service was requested;
     *     ignored.
     * @param string $requestedName Name by which service was requested, must
     *     start with Zend\Navigation\
     * @return Navigation
     */
    public function createServiceWithName(ServiceLocatorInterface $container, $name, $requestedName)
    {
        return $this($container, $requestedName);
    }

    /**
     * Get navigation configuration, if any
     *
     * @param  ContainerInterface $container
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
        if (! isset($config[self::CONFIG_KEY])
            || ! is_array($config[self::CONFIG_KEY])
        ) {
            $this->config = [];
            return $this->config;
        }

        $this->config = $config[self::CONFIG_KEY];
        return $this->config;
    }

    /**
     * Extract config name from service name
     *
     * @param string $name
     * @return string
     */
    private function getConfigName($name)
    {
        return substr($name, strlen(self::SERVICE_PREFIX));
    }

    /**
     * Does the configuration have a matching named section?
     *
     * @param string $name
     * @param array|\ArrayAccess $config
     * @return bool
     */
    private function hasNamedConfig($name, $config)
    {
        $withoutPrefix = $this->getConfigName($name);

        if (isset($config[$withoutPrefix])) {
            return true;
        }

        if (isset($config[strtolower($withoutPrefix)])) {
            return true;
        }

        return false;
    }

    /**
     * Get the matching named configuration section.
     *
     * @param string $name
     * @param array|\ArrayAccess $config
     * @return array
     */
    private function getNamedConfig($name, $config)
    {
        $withoutPrefix = $this->getConfigName($name);

        if (isset($config[$withoutPrefix])) {
            return $config[$withoutPrefix];
        }

        if (isset($config[strtolower($withoutPrefix)])) {
            return $config[strtolower($withoutPrefix)];
        }

        return [];
    }
}
