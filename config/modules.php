<?php

/**
 * IMPORTANT
 *
 * This file is special-cased during the application bootstrapping process to
 * support loading before normal autowiring.
 *
 * Do not add more than is absolutely necessary into this config file.
 *
 * See getModuleManager()
 */

namespace OpenEMR\Modules\Manager;

return [
    ManagerInterface::class => CachingModuleManager::class,
    ModuleManager::class,
    CachingModuleManager::class => fn ($c) => new CachingModuleManager($c->get(ModuleManager::class)),
];
