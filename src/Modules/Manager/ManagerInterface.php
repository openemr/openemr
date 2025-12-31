<?php

declare(strict_types=1);

namespace OpenEMR\Modules\Manager;

interface ManagerInterface
{
    /**
     * Disable the module provided by the specified composer package
     */
    public function disable(string $packageName): void;

    /**
     * Enable the module provided by the specified composer package
     */
    public function enable(string $packageName): void;

    /**
     * Returns all modules that are installed via Composer, indexed by their
     * package name.
     *
     * @return array<string, ModuleInfo>
     */
    public function getAvailableModules(): array;

    /**
     * @return array<string, ModuleInfo>
     */
    public function getEnabledModules(): array;

    public function getInfoFor(string $packageName): ModuleInfo;
}
