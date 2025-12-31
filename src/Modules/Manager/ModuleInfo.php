<?php

declare(strict_types=1);

namespace OpenEMR\Modules\Manager;

use Composer\InstalledVersions;
use OpenEMR\Modules\CliModuleInterface;
use OpenEMR\Modules\ModuleInterface;
use Symfony\Component\Console\Command\Command;

readonly class ModuleInfo
{
    #[\Deprecated]
    public static function for(string $composerPackageName): ModuleInfo
    {
        return new ModuleInfo(
            packageName: $composerPackageName,
            // installDirectory: realpath(InstalledVersions::getInstallPath($composerPackageName)),
            isActive: true,
            entrypoint: \Firehed\OpenemrSampleModule\Init::class, // TODO: composer.json['extras']['oe-module-entrypoint'],
        );
    }

    /**
     * @param class-string<ModuleInterface> $entrypoint
     */
    public function __construct(
        public string $packageName,
        // public string $installDirectory,
        public string $entrypoint,
        public bool $isActive,
    ) {
    }

    /**
     * @return class-string<Command>[]
     */
    public function getCliCommands(): array
    {
        if (is_a($this->entrypoint, CliModuleInterface::class, allow_string: true)) {
            return $this->entrypoint::getConsoleCommands();
        }
        return [];
    }
}
