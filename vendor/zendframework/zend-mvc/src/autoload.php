<?php
/**
 * @see       https://github.com/zendframework/zend-mvc for the canonical source repository
 * @copyright Copyright (c) 2018 Zend Technologies USA Inc. (https://www.zend.com)
 * @license   https://github.com/zendframework/zend-mvc/blob/master/LICENSE.md New BSD License
 */

namespace Zend\Mvc;

use Zend\ServiceManager\PluginManagerInterface;

if (class_exists(PluginManagerInterface::class)) {
    class_alias(Controller\PluginManagerSM3::class, Controller\PluginManager::class, true);
} else {
    class_alias(Controller\PluginManagerSM2::class, Controller\PluginManager::class, true);
}
