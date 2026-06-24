<?php

declare(strict_types=1);

namespace OpenEMR\Plugins;

interface PluginInterface
{
    /**
     * Returns paths to configuration files that define services for dependency
     * injection. These are merged into the application's container
     * configuration when the plugin is active.
     *
     * @return string[] Plugin-root relative file paths
     */
    public static function getConfigFiles(): array;
}
