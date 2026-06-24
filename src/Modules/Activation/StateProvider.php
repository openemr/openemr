<?php

declare(strict_types=1);

namespace OpenEMR\Modules\Activation;

interface StateProvider
{
    /**
     * Check if a module is active.
     *
     * @param string $name The module's package name (e.g. "vendor/module-name")
     */
    public function isActive(string $name): bool;
}
