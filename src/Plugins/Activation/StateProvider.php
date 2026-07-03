<?php

declare(strict_types=1);

namespace OpenEMR\Plugins\Activation;

interface StateProvider
{
    /**
     * Check if a plugin is active.
     *
     * @param string $name The plugin's package name (e.g. "vendor/plugin-name")
     */
    public function isActive(string $name): bool;
}
