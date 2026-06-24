<?php

declare(strict_types=1);

namespace OpenEMR\Plugins;

interface CliPluginInterface extends PluginInterface
{
    /**
     * Provide a list of `doctrine/console` commands that the plugin exposes.
     * This must return the class names of the commands, not instantiated
     * objects.
     *
     * Commands MUST be registered in the DI container
     *
     * Invokble commands (which do not need to extend `Command` nor have an
     * `execute()` method) are preferred.
     *
     * @return class-string[]
     */
    public static function getCommandClasses(): array;
}
