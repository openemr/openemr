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
use Zend\View\Strategy\PhpRendererStrategy;
use Zend\View\View;

class ViewFactory implements FactoryInterface
{
    /**
     * @param  ContainerInterface $container
     * @param  string $name
     * @param  null|array $options
     * @return View
     */
    public function __invoke(ContainerInterface $container, $name, array $options = null)
    {
        $view   = new View();
        $events = $container->get('EventManager');

        $view->setEventManager($events);
        $container->get(PhpRendererStrategy::class)->attach($events);

        return $view;
    }

    /**
     * Create and return View instance
     *
     * For use with zend-servicemanager v2; proxies to __invoke().
     *
     * @param ServiceLocatorInterface $container
     * @return View
     */
    public function createService(ServiceLocatorInterface $container)
    {
        return $this($container, View::class);
    }
}
