<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\ModuleManager\Listener;

use Generator;
use Zend\ModuleManager\ModuleEvent;

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
