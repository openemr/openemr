<?php

/**
 * @see       https://github.com/laminas/laminas-hydrator for the canonical source repository
 * @copyright https://github.com/laminas/laminas-hydrator/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-hydrator/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Laminas\Hydrator;

use Psr\Container\ContainerInterface;

final class StandaloneHydratorPluginManagerFactory
{
    public function __invoke(ContainerInterface $container) : StandaloneHydratorPluginManager
    {
        return new StandaloneHydratorPluginManager();
    }
}
