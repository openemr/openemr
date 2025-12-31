<?php

declare(strict_types=1);

namespace OpenEMR\Modules\Manager;

use Composer\InstalledVersions;

readonly class ModuleInfo
{
    public static function for(string $composerPackageName): ModuleInfo
    {
        return new ModuleInfo(
            packageName: $composerPackageName,
            installDirectory: realpath(InstalledVersions::getInstallPath($composerPackageName)),
            isActive: false,
        );
    }

    public function __construct(
        public string $packageName,
        public string $installDirectory,
        public bool $isActive,
    ) {
    }
}
