<?php

declare(strict_types=1);

use OpenEMR\Modules\Manager\{
    EnableModuleCommand,
    ListModuleCommand,
    ModuleFinder,
    ModuleManager,
};

return [
    EnableModuleCommand::class,
    ListModuleCommand::class,
    ModuleFinder::class,
    ModuleManager::class,
    Psr\Log\LoggerInterface::class => Firehed\SimpleLogger\Stdout::class,
    Firehed\SimpleLogger\Stdout::class,
];
