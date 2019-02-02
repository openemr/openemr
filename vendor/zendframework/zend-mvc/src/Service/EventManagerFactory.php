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
use ReflectionClass;
use Zend\EventManager\EventManager;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class EventManagerFactory implements FactoryInterface
{
    /**
     * Create an EventManager instance
     *
     * Creates a new EventManager instance, seeding it with a shared instance
     * of SharedEventManager.
     *
     * @param  ContainerInterface $container
     * @param  string $name
     * @param  null|array $options
     * @return EventManager
     */
    public function __invoke(ContainerInterface $container, $name, array $options = null)
    {
        if ($this->acceptsSharedManagerToConstructor()) {
            // zend-eventmanager v3
            return new EventManager(
                $container->has('SharedEventManager') ? $container->get('SharedEventManager') : null
            );
        }

        // zend-eventmanager v2
        $events = new EventManager();

        if ($container->has('SharedEventManager')) {
            $events->setSharedManager($container->get('SharedEventManager'));
        }

        return $events;
    }

    /**
     * Create and return EventManager instance
     *
     * For use with zend-servicemanager v2; proxies to __invoke().
     *
     * @param ServiceLocatorInterface $container
     * @return EventManager
     */
    public function createService(ServiceLocatorInterface $container)
    {
        return $this($container, EventManager::class);
    }

    /**
     * Does the EventManager accept the shared manager to the constructor?
     *
     * In zend-eventmanager v3, the EventManager accepts the shared manager
     * instance to the constructor *only*, while in v2, it must be injected
     * via the setSharedManager() method.
     *
     * @return bool
     */
    private function acceptsSharedManagerToConstructor()
    {
        $r = new ReflectionClass(EventManager::class);
        return ! $r->hasMethod('setSharedManager');
    }
}
