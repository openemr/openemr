<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Mvc\Service;

use Interop\Container\ContainerInterface;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\View\Strategy\JsonStrategy;

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

    /**
     * Create and return JsonStrategy instance
     *
     * For use with zend-servicemanager v2; proxies to __invoke().
     *
     * @param ServiceLocatorInterface $container
     * @return JsonStrategy
     */
    public function createService(ServiceLocatorInterface $container)
    {
        return $this($container, JsonStrategy::class);
    }
}
