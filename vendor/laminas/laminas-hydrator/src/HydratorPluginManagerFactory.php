<?php

/**
 * @see       https://github.com/laminas/laminas-hydrator for the canonical source repository
 * @copyright https://github.com/laminas/laminas-hydrator/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-hydrator/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Laminas\Hydrator;

use Laminas\ServiceManager\Config;
use Psr\Container\ContainerInterface;

use function class_exists;
use function is_array;
use function sprintf;

class HydratorPluginManagerFactory
{
    /**
     * Create a HydratorPluginManager instance.
     *
     * If the `config` service is available, and the top-level key `hydrators`
     * exists and is an array, that value will be used to configure the plugin
     * manager. In such cases, the array should follow standard container
     * configuration.
     *
     * @see https://docs.mezzio.dev/mezzio/v3/features/container/config/
     * @throws Exception\DomainException if laminas-servicemanager is not installed.
     */
    public function __invoke(ContainerInterface $container, string $name, ?array $options = []) : HydratorPluginManager
    {
        if (! class_exists(Config::class)) {
            throw new Exception\DomainException(sprintf(
                '%s requires the laminas/laminas-servicemanager package, which is not installed.'
                . ' If you do not want to install that package, you can use the %s instead;'
                . ' however, that version does not have support for the "hydrators"'
                . ' configuration outside of aliases, invokables, and factories. If you'
                . ' need those features, please install laminas/laminas-servicemanager.',
                HydratorPluginManager::class,
                StandaloneHydratorPluginManager::class
            ));
        }

        $pluginManager = new HydratorPluginManager($container, $options ?: []);

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

        // If we do not have hydrators configuration, nothing more to do
        if (! isset($config['hydrators']) || ! is_array($config['hydrators'])) {
            return $pluginManager;
        }

        // Wire service configuration for hydrators
        (new Config($config['hydrators']))->configureServiceManager($pluginManager);

        return $pluginManager;
    }
}
