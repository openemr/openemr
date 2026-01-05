<?php

namespace OpenEMR\Modules\Manager;

return [
    EnableModuleCommand::class,
    ListModuleCommand::class,
    ManagerInterface::class => ModuleManager::class,
    ModuleManager::class,
];
