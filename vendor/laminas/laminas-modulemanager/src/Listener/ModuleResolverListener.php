<?php

/**
 * @see       https://github.com/laminas/laminas-modulemanager for the canonical source repository
 * @copyright https://github.com/laminas/laminas-modulemanager/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-modulemanager/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\ModuleManager\Listener;

use Generator;
use Laminas\ModuleManager\ModuleEvent;

use function class_exists;
use function in_array;
use function sprintf;

/**
 * Module resolver listener
 */
class ModuleResolverListener extends AbstractListener
{
    /**
     * Class names that are invalid as module classes, due to inability to instantiate.
     *
     * @var string[]
     */
    protected $invalidClassNames = [
        Generator::class,
    ];

    /**
     * @param  ModuleEvent $e
     * @return object|false False if module class does not exist
     */
    public function __invoke(ModuleEvent $e)
    {
        $moduleName = $e->getModuleName();

        $class = sprintf('%s\Module', $moduleName);
        if (class_exists($class)) {
            return new $class;
        }

        if (class_exists($moduleName)
            && ! in_array($moduleName, $this->invalidClassNames, true)
        ) {
            return new $moduleName;
        }

        return false;
    }
}
