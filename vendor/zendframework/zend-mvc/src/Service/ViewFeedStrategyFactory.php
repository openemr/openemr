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
use Zend\View\Strategy\FeedStrategy;

class ViewFeedStrategyFactory implements FactoryInterface
{
    /**
     * Create and return the Feed view strategy
     *
     * Retrieves the ViewFeedRenderer service from the service locator, and
     * injects it into the constructor for the feed strategy.
     *
     * It then attaches the strategy to the View service, at a priority of 100.
     *
     * @param  ContainerInterface $container
     * @param  string $name
     * @param  null|array $options
     * @return FeedStrategy
     */
    public function __invoke(ContainerInterface $container, $name, array $options = null)
    {
        return new FeedStrategy($container->get('ViewFeedRenderer'));
    }

    /**
     * Create and return FeedStrategy instance
     *
     * For use with zend-servicemanager v2; proxies to __invoke().
     *
     * @param ServiceLocatorInterface $container
     * @return FeedStrategy
     */
    public function createService(ServiceLocatorInterface $container)
    {
        return $this($container, FeedStrategy::class);
    }
}
