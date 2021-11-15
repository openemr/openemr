<?php

declare(strict_types=1);

/**
 * @see       https://github.com/laminas/laminas-servicemanager for the canonical source repository
 * @copyright https://github.com/laminas/laminas-servicemanager/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-servicemanager/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\ServiceManager\AbstractFactory;

use ArrayObject;
use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Exception\ServiceNotCreatedException;
use Laminas\ServiceManager\Factory\AbstractFactoryInterface;

use function array_key_exists;
use function array_map;
use function array_values;
use function is_array;
use function json_encode;

final class ConfigAbstractFactory implements AbstractFactoryInterface
{

    /**
     * Factory can create the service if there is a key for it in the config
     *
     * {@inheritdoc}
     */
    public function canCreate(ContainerInterface $container, $requestedName)
    {
        if (! $container->has('config')) {
            return false;
        }
        $config = $container->get('config');
        if (! isset($config[self::class])) {
            return false;
        }
        $dependencies = $config[self::class];

        return is_array($dependencies) && array_key_exists($requestedName, $dependencies);
    }

    /**
     * {@inheritDoc}
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        if (! $container->has('config')) {
            throw new ServiceNotCreatedException('Cannot find a config array in the container');
        }

        $config = $container->get('config');

        if (! (is_array($config) || $config instanceof ArrayObject)) {
            throw new ServiceNotCreatedException('Config must be an array or an instance of ArrayObject');
        }
        if (! isset($config[self::class])) {
            throw new ServiceNotCreatedException('Cannot find a `' . self::class . '` key in the config array');
        }


        $dependencies = $config[self::class];

        if (! is_array($dependencies)
            || ! array_key_exists($requestedName, $dependencies)
            || ! is_array($dependencies[$requestedName])
        ) {
            throw new ServiceNotCreatedException('Service dependencies config must exist and be an array');
        }

        $serviceDependencies = $dependencies[$requestedName];

        if ($serviceDependencies !== array_values(array_map('strval', $serviceDependencies))) {
            $problem = json_encode(array_map('gettype', $serviceDependencies));
            throw new ServiceNotCreatedException(
                'Service dependencies config must be an array of strings, ' . $problem . ' given'
            );
        }

        $arguments = array_map([$container, 'get'], $serviceDependencies);

        return new $requestedName(...$arguments);
    }
}
