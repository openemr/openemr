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
     * @return array<string, ModuleInfo>
     */
    public function getEnabledModules(): array;

    /**
     * @return array<string, ModuleInfo>
     */
    public function getAvailableModules(): array;
}
