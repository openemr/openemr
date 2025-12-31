<?php

declare(strict_types=1);

use OpenEMR\Modules\Manager\{
    EnableModuleCommand,
    ListModuleCommand,
    ModuleFinder,
};

return [
    EnableModuleCommand::class,
    ListModuleCommand::class,
    ModuleFinder::class,
    Psr\Log\LoggerInterface::class => Firehed\SimpleLogger\Stdout::class,
    Firehed\SimpleLogger\Stdout::class,
];
