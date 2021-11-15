<?php

/**
 * @see       https://github.com/laminas/laminas-mvc for the canonical source repository
 * @copyright https://github.com/laminas/laminas-mvc/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-mvc/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\Mvc\Service;

use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Laminas\View\Strategy\JsonStrategy;

class ViewJsonStrategyFactory implements FactoryInterface
{
    /**
     * Create and return the JSON view strategy
     *
     * Retrieves the ViewJsonRenderer service from the service locator, and
     * injects it into the constructor for the JSON strategy.
     *
     * It then attaches the strategy to the View service, at a priority of 100.
     *
     * @param  ContainerInterface $container
     * @param  string $name
     * @param  null|array $options
     * @return JsonStrategy
     */
    public function __invoke(ContainerInterface $container, $name, array $options = null)
    {
        $jsonRenderer = $container->get('ViewJsonRenderer');
        $jsonStrategy = new JsonStrategy($jsonRenderer);
        return $jsonStrategy;
    }
}
