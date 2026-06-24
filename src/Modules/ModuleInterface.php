<?php

declare(strict_types=1);

namespace OpenEMR\Modules;

interface ModuleInterface
{
    /**
     * Returns paths to configuration files that define services for dependency
     * injection. These are merged into the application's container
     * configuration when the module is active.
     *
     * @return string[] Module-root relative file paths
     */
    public static function getConfigFiles(): array;
}
