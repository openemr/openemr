<?php

/**
 * @see       https://github.com/laminas/laminas-mvc-console for the canonical source repository
 * @copyright https://github.com/laminas/laminas-mvc-console/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-mvc-console/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\Mvc\Console\Service;

use Interop\Container\ContainerInterface;
use Laminas\Mvc\Console\View\RouteNotFoundStrategy;
use Laminas\ServiceManager\FactoryInterface;
use Laminas\ServiceManager\ServiceLocatorInterface;

class ConsoleRouteNotFoundStrategyFactory implements FactoryInterface
{
    use ConsoleViewManagerConfigTrait;

    /**
     * @param  ContainerInterface $container
     * @param  string $name
     * @param  null|array $options
     * @return RouteNotFoundStrategy
     */
    public function __invoke(ContainerInterface $container, $name, array $options = null)
    {
        $strategy = new RouteNotFoundStrategy();
        $config   = $this->getConfig($container);

        $this->injectDisplayNotFoundReason($strategy, $config);

        return $strategy;
    }

    /**
     * Create and return RouteNotFoundStrategy instance
     *
     * @param ServiceLocatorInterface $container
     * @return RouteNotFoundStrategy
     */
    public function createService(ServiceLocatorInterface $container)
    {
        return $this($container, RouteNotFoundStrategy::class);
    }

    /**
     * Inject strategy with configured display_not_found_reason flag.
     *
     * @param RouteNotFoundStrategy $strategy
     * @param array $config
     */
    private function injectDisplayNotFoundReason(RouteNotFoundStrategy $strategy, array $config)
    {
        $flag = array_key_exists('display_not_found_reason', $config) ? $config['display_not_found_reason'] : true;
        $strategy->setDisplayNotFoundReason($flag);
    }
}
