<?php

declare(strict_types=1);

namespace OpenEMR\Modules\Manager;

use Composer\InstalledVersions;
use OpenEMR\Modules\CliModuleInterface;
use OpenEMR\Modules\ModuleInterface;
use Symfony\Component\Console\Command\Command;

readonly class ModuleInfo
{
    /**
     * @param class-string<ModuleInterface> $entrypoint
     */
    public function __construct(
        public string $packageName,
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

    public function getConfigFiles(): array
    {
        return $this->entrypoint::getConfigFiles();
    }

    /**
     * for var_export in CachingModuleManager
     * @internal
     */
    public static function __set_state(array $data): ModuleInfo
    {
        // TODO: Is this actully stable?
        return new ModuleInfo(...$data);
    }
}
