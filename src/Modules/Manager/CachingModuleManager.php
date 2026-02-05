<?php

declare(strict_types=1);

namespace OpenEMR\Modules\Manager;

class CachingModuleManager implements ManagerInterface
{
    public function __construct(
        private ManagerInterface $manager,
    ) {
    }

    public function disable(string $packageName): void
    {
        $this->expireCache();
        $this->manager->disable($packageName);
    }

    public function enable(string $packageName): void
    {
        $this->expireCache();
        $this->manager->enable($packageName);
    }

    public function getAvailableModules(): array
    {
        if (file_exists(self::CACHE_FILE)) {
            return require self::CACHE_FILE;
        }

        $data = $this->manager->getAvailableModules();

        $php = sprintf('<?php return %s;', var_export($data, true));
        file_put_contents(self::CACHE_FILE, $php);

        return $data;
    }

    public function getEnabledModules(): array
    {
        return array_filter($this->getAvailableModules(), fn ($m) => $m->isActive);
    }

    public function getInfoFor(string $packageName): ModuleInfo
    {
        return $this->manager->getInfoFor($packageName);
    }

    private function expireCache(): void
    {
        if (file_exists(self::CACHE_FILE)) {
            unlink(self::CACHE_FILE);
        }
    }

    private const CACHE_FILE = 'vendor/module_status.php';
}
