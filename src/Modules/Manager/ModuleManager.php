<?php

declare(strict_types=1);

namespace OpenEMR\Modules\Manager;

use Composer\InstalledVersions;
use OpenEMR\Modules\{
    CliModuleInterface,
    ModuleInterface,
};

class ModuleManager implements ModuleInterface, CliModuleInterface
{
    public static function getConsoleCommands(): array
    {
        return [
            EnableModuleCommand::class,
            ListModuleCommand::class,
        ];
    }

    private const /* string */ MANIFEST_FILE = 'todooooo.php';

    public function enable(string $packageName): void
    {
        // create the manifest file if it doesn't exist
        // find all of the ModuleInterface classes within the module
        // write them into the manifest
    }

    public function disable(string $packageName): void
    {
        // Block disabling the manager itself.
        // Otherwise, undo manifest changes from enable.
    }

    public function getInfoFor(string $packageName): ModuleInfo
    {
        // FIXME: not like this.
        return ModuleInfo::for($packageName);
    }

    /**
     * @return ModuleInfo[]
     */
    public function getEnabledModules(): array
    {
        // TODO: while FS-based installs are OK, this needs to support other
        // enable/disable mechanisms for other deployment systems
        // e.g. OPENEMR_ENABLE_MODULES=vendor/pkg1,vendor/pkg2,...
        if (!file_exists(self::MANIFEST_FILE)) {
            // ensure manager itself loads
            return [self::getManagerModuleInfo()];
        }
        return [];
    }

    /**
     * @return ModuleInfo[]
     */
    public function getInstalledModules(): array
    {
        $packages = InstalledVersions::getInstalledPackagesByType(ModuleInterface::COMPOSER_TYPE);
        $info = array_map(ModuleInfo::for(...), $packages);
        $info[] = self::getManagerModuleInfo();
        // sort by name?
        return $info;
    }

    private static function getManagerModuleInfo(): ModuleInfo
    {
        return new ModuleInfo(
            packageName: 'opememr/module-manager',
            installDirectory: 'idk',
            isActive: true,
        );
    }
}
