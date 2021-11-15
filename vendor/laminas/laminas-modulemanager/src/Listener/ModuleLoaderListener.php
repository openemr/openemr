<?php

/**
 * @see       https://github.com/laminas/laminas-modulemanager for the canonical source repository
 * @copyright https://github.com/laminas/laminas-modulemanager/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-modulemanager/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\ModuleManager\Listener;

use Laminas\EventManager\EventManagerInterface;
use Laminas\EventManager\ListenerAggregateInterface;
use Laminas\Loader\ModuleAutoloader;
use Laminas\ModuleManager\ModuleEvent;

use function file_exists;

/**
 * Module loader listener
 */
class ModuleLoaderListener extends AbstractListener implements ListenerAggregateInterface
{
    /**
     * @var ModuleAutoloader
     */
    protected $moduleLoader;

    /**
     * @var bool
     */
    protected $generateCache;

    /**
     * @var array
     */
    protected $callbacks = [];

    /**
     * Constructor.
     *
     * Creates an instance of the ModuleAutoloader and injects the module paths
     * into it.
     *
     * @param  ListenerOptions $options
     */
    public function __construct(ListenerOptions $options = null)
    {
        parent::__construct($options);

        $this->generateCache = $this->options->getModuleMapCacheEnabled();
        $this->moduleLoader  = new ModuleAutoloader($this->options->getModulePaths());

        if ($this->hasCachedClassMap()) {
            $this->generateCache = false;
            $this->moduleLoader->setModuleClassMap($this->getCachedConfig());
        }
    }

    /**
     * {@inheritDoc}
     */
    public function attach(EventManagerInterface $events, $priority = 1)
    {
        $this->callbacks[] = $events->attach(
            ModuleEvent::EVENT_LOAD_MODULES,
            [$this->moduleLoader, 'register'],
            9000
        );

        if ($this->generateCache) {
            $this->callbacks[] = $events->attach(
                ModuleEvent::EVENT_LOAD_MODULES_POST,
                [$this, 'onLoadModulesPost']
            );
        }
    }

    /**
     * {@inheritDoc}
     */
    public function detach(EventManagerInterface $events)
    {
        foreach ($this->callbacks as $index => $callback) {
            if ($events->detach($callback)) {
                unset($this->callbacks[$index]);
            }
        }
    }

    /**
     * @return bool
     */
    protected function hasCachedClassMap()
    {
        if ($this->options->getModuleMapCacheEnabled()
            && file_exists($this->options->getModuleMapCacheFile())
        ) {
            return true;
        }

        return false;
    }

    /**
     * @return array
     */
    protected function getCachedConfig()
    {
        return include $this->options->getModuleMapCacheFile();
    }

    /**
     * loadModulesPost
     *
     * Unregisters the ModuleLoader and generates the module class map cache.
     *
     * @param  ModuleEvent $event
     */
    public function onLoadModulesPost(ModuleEvent $event)
    {
        $this->moduleLoader->unregister();
        $this->writeArrayToFile(
            $this->options->getModuleMapCacheFile(),
            $this->moduleLoader->getModuleClassMap()
        );
    }
}
