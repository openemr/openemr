<?php

declare(strict_types=1);

namespace OpenEMR\Modules;

use Symfony\Component\Console\Command\Command;

interface CliModuleInterface extends ModuleInterface
{
    /**
     * @return class-string<Command>[]
     */
    public static function getConsoleCommands(): array;
}
