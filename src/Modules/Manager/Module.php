<?php

declare(strict_types=1);

namespace OpenEMR\Modules\Manager;

use OpenEMR\Modules\{
    CliModuleInterface,
    ModuleInterface,
};

/**
 * This is the actual entrypoint for the module-management module.
 */
class Module implements ModuleInterface, CliModuleInterface
{
    public static function getConsoleCommands(): array
    {
        return [
            EnableModuleCommand::class,
            ListModuleCommand::class,
        ];
    }

    public static function getConfigFiles(): array
    {
        return [
            __DIR__ . '/config.php',
        ];
    }
}
