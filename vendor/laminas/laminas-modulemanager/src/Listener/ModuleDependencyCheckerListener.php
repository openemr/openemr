<?php

/**
 * @see       https://github.com/laminas/laminas-modulemanager for the canonical source repository
 * @copyright https://github.com/laminas/laminas-modulemanager/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-modulemanager/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\ModuleManager\Listener;

use Laminas\ModuleManager\Exception;
use Laminas\ModuleManager\Feature\DependencyIndicatorInterface;
use Laminas\ModuleManager\ModuleEvent;

use function method_exists;
use function sprintf;

/**
 * Module resolver listener
 */
class ModuleDependencyCheckerListener
{
    /**
     * @var array of already loaded modules, indexed by module name
     */
    protected $loaded = [];

    /**
     * @param \Laminas\ModuleManager\ModuleEvent $e
     *
     * @throws Exception\MissingDependencyModuleException
     */
    public function __invoke(ModuleEvent $e)
    {
        $module = $e->getModule();

        if ($module instanceof DependencyIndicatorInterface || method_exists($module, 'getModuleDependencies')) {
            $dependencies = $module->getModuleDependencies();

            foreach ($dependencies as $dependencyModule) {
                if (! isset($this->loaded[$dependencyModule])) {
                    throw new Exception\MissingDependencyModuleException(
                        sprintf(
                            'Module "%s" depends on module "%s", which was not initialized before it',
                            $e->getModuleName(),
                            $dependencyModule
                        )
                    );
                }
            }
        }

        $this->loaded[$e->getModuleName()] = true;
    }
}
