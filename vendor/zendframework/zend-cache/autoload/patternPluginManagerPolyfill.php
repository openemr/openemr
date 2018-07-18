<?php
/**
 * @see       https://github.com/zendframework/zend-cache for the canonical source repository
 * @copyright Copyright (c) 2017 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   https://github.com/zendframework/zend-cache/blob/master/LICENSE.md New BSD License
 */

use Zend\Cache\PatternPluginManager;
use Zend\ServiceManager\ServiceManager;

call_user_func(function () {
    $target = method_exists(ServiceManager::class, 'configure')
        ? PatternPluginManager\PatternPluginManagerV3Polyfill::class
        : PatternPluginManager\PatternPluginManagerV2Polyfill::class;

    class_alias($target, PatternPluginManager::class);
});
