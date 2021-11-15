<?php

declare(strict_types=1);

/**
 * @see       https://github.com/laminas/laminas-servicemanager for the canonical source repository
 * @copyright https://github.com/laminas/laminas-servicemanager/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-servicemanager/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\ServiceManager\Factory;

use Interop\Container\ContainerInterface;

/**
 * Interface for an abstract factory.
 *
 * An abstract factory extends the factory interface, but also has an
 * additional "canCreate" method, which is called to check if the abstract
 * factory has the ability to create an instance for the given service. You
 * should limit the number of abstract factories to ensure good performance.
 * Starting from ServiceManager v3, remember that you can also attach multiple
 * names to the same factory, which reduces the need for abstract factories.
 */
interface AbstractFactoryInterface extends FactoryInterface
{
    /**
     * Can the factory create an instance for the service?
     *
     * @param  ContainerInterface $container
     * @param  string $requestedName
     * @return bool
     */
    public function canCreate(ContainerInterface $container, $requestedName);
}
