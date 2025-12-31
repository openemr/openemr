<?php

declare(strict_types=1);

use OpenEMR\Modules\Manager\{
    EnableModuleCommand,
    ListModuleCommand,
    ManagerInterface,
    ModuleFinder,
    ModuleManager,
};

return [
    EnableModuleCommand::class,
    ListModuleCommand::class,
    ModuleFinder::class,
    ManagerInterface::class => ModuleManager::class,
    ModuleManager::class,
    Psr\Log\LoggerInterface::class => Firehed\SimpleLogger\Stdout::class,
    Firehed\SimpleLogger\Stdout::class,
];
