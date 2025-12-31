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

    public function getEnabledModules(): array
    {
        if (!file_exists(self::MANIFEST_FILE)) {
            // ensure manager itself loads
            return [
                'openemr/module-mamanger',
            ];
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
        $info[] = new ModuleInfo(
            packageName: 'opememr/module-manager',
            installDirectory: 'idk',
            isActive: true,
        );
        // sort by name?
        return $info;
    }
}
