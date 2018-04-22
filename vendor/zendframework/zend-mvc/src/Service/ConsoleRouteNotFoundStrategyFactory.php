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
use Zend\Mvc\View\Console\RouteNotFoundStrategy;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

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
