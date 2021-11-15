<?php

/**
 * @see       https://github.com/laminas/laminas-modulemanager for the canonical source repository
 * @copyright https://github.com/laminas/laminas-modulemanager/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-modulemanager/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\ModuleManager\Listener;

use Laminas\EventManager\EventManagerInterface;
use Laminas\EventManager\ListenerAggregateInterface;
use Laminas\EventManager\ListenerAggregateTrait;
use Laminas\ModuleManager\Feature\LocatorRegisteredInterface;
use Laminas\ModuleManager\ModuleEvent;
use Laminas\ModuleManager\ModuleManager;
use Laminas\Mvc\MvcEvent;
use Laminas\ServiceManager\ServiceManager;

use function end;
use function explode;
use function get_class;

/**
 * Locator registration listener
 */
class LocatorRegistrationListener extends AbstractListener implements
    ListenerAggregateInterface
{
    use ListenerAggregateTrait;

    /**
     * @var array
     */
    protected $modules = [];

    /**
     * loadModule
     *
     * Check each loaded module to see if it implements LocatorRegistered. If it
     * does, we add it to an internal array for later.
     *
     * @param  ModuleEvent $e
     * @return void
     */
    public function onLoadModule(ModuleEvent $e)
    {
        if (! $e->getModule() instanceof LocatorRegisteredInterface) {
            return;
        }
        $this->modules[] = $e->getModule();
    }

    /**
     * loadModules
     *
     * Once all the modules are loaded, loop
     *
     * @param  ModuleEvent $e
     * @return void
     */
    public function onLoadModules(ModuleEvent $e)
    {
        $moduleManager = $e->getTarget();
        $events        = $moduleManager->getEventManager()->getSharedManager();

        if (! $events) {
            return;
        }

        // Shared instance for module manager
        $events->attach(
            'Laminas\Mvc\Application',
            ModuleManager::EVENT_BOOTSTRAP,
            function (MvcEvent $e) use ($moduleManager) {
                $moduleClassName      = get_class($moduleManager);
                $moduleClassNameArray = explode('\\', $moduleClassName);
                $moduleClassNameAlias = end($moduleClassNameArray);
                $application          = $e->getApplication();
                /** @var $services ServiceManager */
                $services             = $application->getServiceManager();
                if (! $services->has($moduleClassName)) {
                        $services->setAlias($moduleClassName, $moduleClassNameAlias);
                }
            },
            1000
        );

        if (! $this->modules) {
            return;
        }

        // Attach to the bootstrap event if there are modules we need to process
        $events->attach('Laminas\Mvc\Application', ModuleManager::EVENT_BOOTSTRAP, [$this, 'onBootstrap'], 1000);
    }

    /**
     * Bootstrap listener
     *
     * This is ran during the MVC bootstrap event because it requires access to
     * the DI container.
     *
     * @TODO: Check the application / locator / etc a bit better to make sure
     * the env looks how we're expecting it to?
     * @param MvcEvent $e
     * @return void
     */
    public function onBootstrap(MvcEvent $e)
    {
        $application = $e->getApplication();
        /** @var $services ServiceManager */
        $services    = $application->getServiceManager();

        foreach ($this->modules as $module) {
            $moduleClassName = get_class($module);
            if (! $services->has($moduleClassName)) {
                $services->setService($moduleClassName, $module);
            }
        }
    }

    /**
     * {@inheritDoc}
     */
    public function attach(EventManagerInterface $events, $priority = 1)
    {
        $this->listeners[] = $events->attach(ModuleEvent::EVENT_LOAD_MODULE, [$this, 'onLoadModule']);
        $this->listeners[] = $events->attach(ModuleEvent::EVENT_LOAD_MODULES, [$this, 'onLoadModules'], -1000);
    }
}
