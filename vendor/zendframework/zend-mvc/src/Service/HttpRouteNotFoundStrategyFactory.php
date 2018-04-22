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
use Zend\Mvc\View\Http\RouteNotFoundStrategy;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class HttpRouteNotFoundStrategyFactory implements FactoryInterface
{
    use HttpViewManagerConfigTrait;

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

        $this->injectDisplayExceptions($strategy, $config);
        $this->injectDisplayNotFoundReason($strategy, $config);
        $this->injectNotFoundTemplate($strategy, $config);

        return $strategy;
    }

    /**
     * Create and return RouteNotFoundStrategy instance
     *
     * For use with zend-servicemanager v2; proxies to __invoke().
     *
     * @param ServiceLocatorInterface $container
     * @return RouteNotFoundStrategy
     */
    public function createService(ServiceLocatorInterface $container)
    {
        return $this($container, RouteNotFoundStrategy::class);
    }

    /**
     * Inject strategy with configured display_exceptions flag.
     *
     * @param RouteNotFoundStrategy $strategy
     * @param array $config
     */
    private function injectDisplayExceptions(RouteNotFoundStrategy $strategy, array $config)
    {
        $flag = isset($config['display_exceptions']) ? $config['display_exceptions'] : false;
        $strategy->setDisplayExceptions($flag);
    }

    /**
     * Inject strategy with configured display_not_found_reason flag.
     *
     * @param RouteNotFoundStrategy $strategy
     * @param array $config
     */
    private function injectDisplayNotFoundReason(RouteNotFoundStrategy $strategy, array $config)
    {
        $flag = isset($config['display_not_found_reason']) ? $config['display_not_found_reason'] : false;
        $strategy->setDisplayNotFoundReason($flag);
    }

    /**
     * Inject strategy with configured not_found_template.
     *
     * @param RouteNotFoundStrategy $strategy
     * @param array $config
     */
    private function injectNotFoundTemplate(RouteNotFoundStrategy $strategy, array $config)
    {
        $template = isset($config['not_found_template']) ? $config['not_found_template'] : '404';
        $strategy->setNotFoundTemplate($template);
    }
}
