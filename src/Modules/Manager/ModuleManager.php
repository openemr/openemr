<?php

declare(strict_types=1);

namespace OpenEMR\Modules\Manager;

use Composer\InstalledVersions;
use OpenEMR\Modules\ModuleInterface;

class ModuleManager implements ManagerInterface
{
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

    /**
     * Returns module info for a given Composer package. Throws an exception if
     * the package is not installed or is not a module.
     */
    public function getInfoFor(string $packageName): ModuleInfo
    {
        $installed = $this->getAvailableModules();
        if (!array_key_exists($packageName, $installed)) {
            throw new \Exception('Package does not exist');
        }
        return $installed[$packageName];
    }

    /**
     * This implementation needs to be kept as light as possible since the
     * method will be called early in the lifecycle of nearly every request.
     *
     * @return ModuleInfo[]
     */
    public function getEnabledModules(): array
    {
        //// TODO: while FS-based installs are OK, this needs to support other
        //// enable/disable mechanisms for other deployment systems
        //// e.g. OPENEMR_ENABLE_MODULES=vendor/pkg1,vendor/pkg2,...
        ////
        //// Support a driver-based system and delegate to it:
        //// - env
        //// - static file
        //// - database
        //// more?
        //if (!file_exists(self::MANIFEST_FILE)) {
        //    // ensure manager itself loads
        //    return [self::getManagerModuleInfo()];
        //}
        //return [];
        return array_filter($this->getAvailableModules(), fn ($m) => $m->isActive);
    }

    /**
     * @return array<string, ModuleInfo>
     */
    public function getAvailableModules(): array
    {
        $packages = InstalledVersions::getInstalledPackagesByType(ModuleInterface::COMPOSER_TYPE);
        $info = [];
        foreach ($packages as $package) {
            // TODO: this should determine the critical module info instead of
            // using ModuleInfo::for()
            $info[$package] = ModuleInfo::for($package);
        }
        $info['openemr/module-manager'] = self::getManagerModuleInfo();
        return $info;
    }

    private static function getManagerModuleInfo(): ModuleInfo
    {
        return new ModuleInfo(
            packageName: 'opememr/module-manager',
            entrypoint: Module::class,
            // installDirectory: 'idk', // dirname(__DIR__)?
            isActive: true,
        );
    }
}
