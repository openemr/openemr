<?php

/**
 * @see       https://github.com/laminas/laminas-modulemanager for the canonical source repository
 * @copyright https://github.com/laminas/laminas-modulemanager/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-modulemanager/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\ModuleManager\Listener;

use Laminas\EventManager\EventManagerInterface;
use Laminas\EventManager\ListenerAggregateInterface;
use Laminas\ModuleManager\ModuleEvent;

/**
 * Default listener aggregate
 */
class DefaultListenerAggregate extends AbstractListener implements
    ListenerAggregateInterface
{
    /**
     * @var array
     */
    protected $listeners = [];

    /**
     * @var ConfigMergerInterface
     */
    protected $configListener;

    /**
     * Attach one or more listeners
     *
     * @param  EventManagerInterface $events
     * @param  int $priority
     * @return DefaultListenerAggregate
     */
    public function attach(EventManagerInterface $events, $priority = 1)
    {
        $options                     = $this->getOptions();
        $configListener              = $this->getConfigListener();
        $locatorRegistrationListener = new LocatorRegistrationListener($options);

        // High priority, we assume module autoloading (for FooNamespace\Module
        // classes) should be available before anything else.
        // Register it only if use_laminas_loader config is true, however.
        if ($options->useLaminasLoader()) {
            $moduleLoaderListener = new ModuleLoaderListener($options);
            $moduleLoaderListener->attach($events);
            $this->listeners[] = $moduleLoaderListener;
        }
        $this->listeners[] = $events->attach(ModuleEvent::EVENT_LOAD_MODULE_RESOLVE, new ModuleResolverListener);

        if ($options->useLaminasLoader()) {
            // High priority, because most other loadModule listeners will assume
            // the module's classes are available via autoloading
            // Register it only if use_laminas_loader config is true, however.
            $this->listeners[] = $events->attach(
                ModuleEvent::EVENT_LOAD_MODULE,
                new AutoloaderListener($options),
                9000
            );
        }

        if ($options->getCheckDependencies()) {
            $this->listeners[] = $events->attach(
                ModuleEvent::EVENT_LOAD_MODULE,
                new ModuleDependencyCheckerListener,
                8000
            );
        }

        $this->listeners[] = $events->attach(ModuleEvent::EVENT_LOAD_MODULE, new InitTrigger($options));
        $this->listeners[] = $events->attach(ModuleEvent::EVENT_LOAD_MODULE, new OnBootstrapListener($options));

        $locatorRegistrationListener->attach($events);
        $configListener->attach($events);
        $this->listeners[] = $locatorRegistrationListener;
        $this->listeners[] = $configListener;
        return $this;
    }

    /**
     * Detach all previously attached listeners
     *
     * @param  EventManagerInterface $events
     * @return void
     */
    public function detach(EventManagerInterface $events)
    {
        foreach ($this->listeners as $key => $listener) {
            if ($listener instanceof ListenerAggregateInterface) {
                $listener->detach($events);
                unset($this->listeners[$key]);
                continue;
            }

            $events->detach($listener);
            unset($this->listeners[$key]);
        }
    }

    /**
     * Get the config merger.
     *
     * @return ConfigMergerInterface
     */
    public function getConfigListener()
    {
        if (! $this->configListener instanceof ConfigMergerInterface) {
            $this->setConfigListener(new ConfigListener($this->getOptions()));
        }
        return $this->configListener;
    }

    /**
     * Set the config merger to use.
     *
     * @param  ConfigMergerInterface $configListener
     * @return DefaultListenerAggregate
     */
    public function setConfigListener(ConfigMergerInterface $configListener)
    {
        $this->configListener = $configListener;
        return $this;
    }
}
