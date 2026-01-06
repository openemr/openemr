<?php

declare(strict_types=1);

namespace OpenEMR\Modules\Manager;

use Composer\{
    Factory,
    IO\NullIO,
    InstalledVersions,
};
use OpenEMR\Modules\ModuleInterface;

/**
 * TODO: FileModuleManager? (could also support db, env var, etc
 */
class ModuleManager implements ManagerInterface
{
    /**
     * Special-case the tooling for module management. This may move out to
     * being a separate package (but still core dependency) eventually.
     */
    private const MODULE_MANAGER_NAME = 'openemr/module-manager';

    // private const MANIFEST_FILE = 'module_manifest.php';

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
        // TODO: cache this or availability or whatever. APCu or a simple file
        // is fine.
        return array_filter($this->getAvailableModules(), fn ($m) => $m->isActive);
    }

    public function getAvailableModules(): array
    {
        $modules = [
            self::MODULE_MANAGER_NAME => self::getManagerModuleInfo(),
        ];
        foreach (self::findComposerModules() as $pkg => $ep) {
            $modules[$pkg] = new ModuleInfo(
                packageName: $pkg,
                entrypoint: $ep,
                // TODO: actually read the activation status
                isActive: false,
            );
        }
        return $modules;
    }

    private static function getManagerModuleInfo(): ModuleInfo
    {
        return new ModuleInfo(
            packageName: self::MODULE_MANAGER_NAME,
            entrypoint: Module::class,
            isActive: true,
        );
    }

    /**
     * @return array<string, class-string<ModuleInterface>>
     */
    private static function findComposerModules(): array
    {
        $packages = InstalledVersions::getInstalledPackagesByType(ModuleInterface::COMPOSER_TYPE);

        // This is a somewhat convoluted way of reading composer.lock and
        // extracting metadata. A future version of this module system may use
        // composer-level installer hooks to streamline this process.
        $composer = Factory::create(new NullIO());
        $locker = $composer->getLocker();
        $lockData = $locker->getLockData();
        $allInstalled = $lockData['packages'];

        $availableModules = [];

        foreach ($packages as $package) {
            $composerInfo = array_find($allInstalled, fn ($p) => $p['name'] === $package);
            $extra = $composerInfo['extra'] ?? [];
            $entrypoint = $extra[ModuleInterface::ENTRYPOINT_KEY] ?? '';
            if (!is_string($entrypoint) || $entrypoint === '') {
                // echo "Bad entrypoint";
                continue;
            }
            // Future: this should do more to validate that the entrypoint is
            // actually within the module.

            if (!class_exists($entrypoint)) {
                // echo "Entrypoint is not a class";
                continue;
            }
            if (!is_a($entrypoint, ModuleInterface::class, allow_string: true)) {
                // echo "EP not a module";
                continue;
            }

            $availableModules[$package] = $entrypoint;
        }
        return $availableModules;
    }
}
