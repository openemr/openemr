<?php

declare(strict_types=1);

namespace OpenEMR\Modules\MedEx;

final class ReconcileManager
{
    public const PLATFORM = 'openemr';
    public const BASE_PACKAGE_ID = 'medex.base';

    private UpdateManager $updateManager;
    private string $moduleDir;

    public function __construct(?UpdateManager $updateManager = null)
    {
        $this->updateManager = $updateManager ?? new UpdateManager();
        $this->moduleDir = realpath(__DIR__ . '/..') ?: (__DIR__ . '/..');
    }

    /**
     * @return array<string,mixed>
     */
    public function getLocalManifest(): array
    {
        $packages = [
            self::BASE_PACKAGE_ID => [
                'package_id' => self::BASE_PACKAGE_ID,
                'package_type' => 'bootstrap',
                'title' => 'MedEx Bootstrap',
                'version' => $this->updateManager->getCurrentVersion(),
            ],
        ];

        foreach (ComponentLoader::manifests() as $manifest) {
            $package = $this->normalizeLocalComponentPackage($manifest);
            if ($package === null) {
                continue;
            }
            $packages[$package['package_id']] = $package;
        }

        return [
            'platform' => self::PLATFORM,
            'module' => 'medex',
            'packages' => array_values($packages),
        ];
    }

    /**
     * @param array<string,mixed> $desiredManifest
     * @return array<string,mixed>
     */
    public function buildPlan(array $desiredManifest): array
    {
        $desiredPackages = $this->normalizeDesiredPackages((array)($desiredManifest['packages'] ?? []));
        $localPackages = $this->mapPackagesById((array)($this->getLocalManifest()['packages'] ?? []));

        $install = [];
        $update = [];
        $remove = [];

        foreach ($desiredPackages as $packageId => $desired) {
            $local = $localPackages[$packageId] ?? null;
            if ($local === null) {
                $install[] = $desired;
                continue;
            }

            $desiredVersion = (string)($desired['version'] ?? '');
            $localVersion = (string)($local['version'] ?? '');
            if ($desiredVersion !== '' && $localVersion !== '' && version_compare($desiredVersion, $localVersion, '!=')) {
                $update[] = array_merge($desired, [
                    'installed_version' => $localVersion,
                ]);
            }
        }

        foreach ($localPackages as $packageId => $local) {
            if ($packageId === self::BASE_PACKAGE_ID) {
                continue;
            }
            if (!isset($desiredPackages[$packageId])) {
                $remove[] = $local;
            }
        }

        return [
            'platform' => self::PLATFORM,
            'install' => $install,
            'update' => $update,
            'remove' => $remove,
            'requires_reconcile' => !empty($install) || !empty($update) || !empty($remove),
            'counts' => [
                'install' => count($install),
                'update' => count($update),
                'remove' => count($remove),
            ],
        ];
    }

    /**
     * @param array<string,mixed> $plan
     * @return array<string,mixed>
     */
    public function applyPlan(array $plan): array
    {
        $this->ensureStateColumns();
        $state = $this->readInstalledPackageState();
        $packageState = is_array($state['packages'] ?? null) ? $state['packages'] : [];
        $steps = [];

        foreach ((array)($plan['remove'] ?? []) as $package) {
            if (!is_array($package)) {
                continue;
            }
            $packageId = trim((string)($package['package_id'] ?? ''));
            if ($packageId === '') {
                continue;
            }
            $removedFiles = $this->removePackageFiles((array)($packageState[$packageId]['files'] ?? []));
            unset($packageState[$packageId]);
            $steps[] = [
                'action' => 'remove',
                'package_id' => $packageId,
                'removed_files' => $removedFiles,
            ];
        }

        foreach (['install', 'update'] as $action) {
            foreach ((array)($plan[$action] ?? []) as $package) {
                if (!is_array($package)) {
                    continue;
                }
                $packageId = trim((string)($package['package_id'] ?? ''));
                $packageUrl = trim((string)($package['package_url'] ?? ''));
                if ($packageId === '' || $packageUrl === '') {
                    continue;
                }

                if ($action === 'update' && !empty($packageState[$packageId]['files'])) {
                    $this->removePackageFiles((array)$packageState[$packageId]['files']);
                }

                $tempFile = $this->updateManager->downloadPackage($packageUrl);
                if (!$tempFile) {
                    return [
                        'success' => false,
                        'error' => 'Failed to download package: ' . $packageId,
                        'steps' => $steps,
                    ];
                }

                try {
                    $installedFiles = $this->installPackageZip($tempFile);
                } finally {
                    if (is_file($tempFile)) {
                        unlink($tempFile);
                    }
                }

                $packageState[$packageId] = [
                    'package_id' => $packageId,
                    'package_key' => (string)($package['package_key'] ?? ''),
                    'version' => (string)($package['version'] ?? ''),
                    'files' => $installedFiles,
                    'installed_at' => gmdate('c'),
                ];

                $steps[] = [
                    'action' => $action,
                    'package_id' => $packageId,
                    'version' => (string)($package['version'] ?? ''),
                    'installed_files' => count($installedFiles),
                ];
            }
        }

        $newState = [
            'packages' => $packageState,
            'updated_at' => gmdate('c'),
        ];
        $this->writeInstalledPackageState($newState);

        return [
            'success' => true,
            'steps' => $steps,
            'state' => $newState,
        ];
    }

    /**
     * @param array<string,mixed> $manifest
     * @return array<string,mixed>|null
     */
    private function normalizeLocalComponentPackage(array $manifest): ?array
    {
        $packageId = trim((string)($manifest['package_id'] ?? ''));
        if ($packageId === '') {
            $key = trim((string)($manifest['key'] ?? ''));
            if ($key === '') {
                return null;
            }
            $packageId = 'medex.component.' . str_replace('-', '_', preg_replace('/^component-/', '', $key) ?? $key);
        }

        $version = trim((string)($manifest['package_version'] ?? $manifest['version'] ?? ''));
        if ($version === '') {
            $version = $this->updateManager->getCurrentVersion();
        }

        return [
            'package_id' => $packageId,
            'package_type' => 'component',
            'package_key' => (string)($manifest['key'] ?? ''),
            'title' => (string)($manifest['title'] ?? $packageId),
            'version' => $version,
            'aliases' => array_values((array)($manifest['aliases'] ?? [])),
        ];
    }

    /**
     * @param array<int,array<string,mixed>> $packages
     * @return array<string,array<string,mixed>>
     */
    private function normalizeDesiredPackages(array $packages): array
    {
        $normalized = [];
        foreach ($packages as $package) {
            if (!is_array($package)) {
                continue;
            }
            $packageId = trim((string)($package['package_id'] ?? ''));
            if ($packageId === '') {
                continue;
            }
            $normalized[$packageId] = [
                'package_id' => $packageId,
                'package_type' => (string)($package['package_type'] ?? 'component'),
                'package_key' => (string)($package['package_key'] ?? ''),
                'title' => (string)($package['title'] ?? $packageId),
                'version' => (string)($package['version'] ?? ''),
                'package_url' => (string)($package['package_url'] ?? ''),
                'sha256' => (string)($package['sha256'] ?? ''),
                'dependencies' => array_values((array)($package['dependencies'] ?? [])),
                'active_services' => array_values((array)($package['active_services'] ?? [])),
            ];
        }
        return $normalized;
    }

    /**
     * @param array<int,array<string,mixed>> $packages
     * @return array<string,array<string,mixed>>
     */
    private function mapPackagesById(array $packages): array
    {
        $mapped = [];
        foreach ($packages as $package) {
            if (!is_array($package)) {
                continue;
            }
            $packageId = trim((string)($package['package_id'] ?? ''));
            if ($packageId === '') {
                continue;
            }
            $mapped[$packageId] = $package;
        }
        return $mapped;
    }

    private function ensureStateColumns(): void
    {
        try {
            $cols = \OpenEMR\Common\Database\QueryUtils::fetchRecords("SHOW COLUMNS FROM medex_prefs LIKE 'package_state_json'");
            if (empty($cols)) {
                \OpenEMR\Common\Database\QueryUtils::sqlStatementThrowException(
                    "ALTER TABLE medex_prefs
                        ADD COLUMN package_state_json LONGTEXT NULL,
                        ADD COLUMN package_state_updated DATETIME NULL"
                );
            }
        } catch (\Throwable $e) {
            error_log('[MedEx Reconcile] Failed to ensure package state columns: ' . $e->getMessage());
        }
    }

    /**
     * @return array<string,mixed>
     */
    private function readInstalledPackageState(): array
    {
        try {
            $records = \OpenEMR\Common\Database\QueryUtils::fetchRecords(
                "SELECT package_state_json FROM medex_prefs LIMIT 1"
            );
            $row = $records[0] ?? null;
            if (!$row || empty($row['package_state_json'])) {
                return [];
            }
            $decoded = json_decode((string)$row['package_state_json'], true);
            return is_array($decoded) ? $decoded : [];
        } catch (\Throwable $e) {
            error_log('[MedEx Reconcile] Failed to read package state: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * @param array<string,mixed> $state
     */
    private function writeInstalledPackageState(array $state): void
    {
        try {
            \OpenEMR\Common\Database\QueryUtils::sqlStatementThrowException(
                "UPDATE medex_prefs SET package_state_json = ?, package_state_updated = NOW() WHERE 1",
                [json_encode($state)]
            );
        } catch (\Throwable $e) {
            error_log('[MedEx Reconcile] Failed to write package state: ' . $e->getMessage());
        }
    }

    /**
     * @return array<int,string>
     */
    private function installPackageZip(string $zipFile): array
    {
        $zip = new \ZipArchive();
        if ($zip->open($zipFile) !== true) {
            throw new \RuntimeException('Failed to open package zip');
        }

        $files = [];
        for ($i = 0; $i < $zip->numFiles; $i++) {
            $name = (string)$zip->getNameIndex($i);
            $normalized = $this->normalizeArchivePath($name);
            if ($normalized === null) {
                $zip->close();
                throw new \RuntimeException('Unsafe package path: ' . $name);
            }
            if (substr($normalized, -1) === '/') {
                continue;
            }
            $files[] = $normalized;
        }

        if (!$zip->extractTo($this->moduleDir)) {
            $zip->close();
            throw new \RuntimeException('Failed to extract package zip');
        }
        $zip->close();

        return array_values(array_unique($files));
    }

    /**
     * @param array<int,string> $files
     * @return int
     */
    private function removePackageFiles(array $files): int
    {
        $removed = 0;
        foreach ($files as $relativePath) {
            $normalized = $this->normalizeArchivePath((string)$relativePath);
            if ($normalized === null || substr($normalized, -1) === '/') {
                continue;
            }

            $absolute = $this->moduleDir . '/' . $normalized;
            if (is_file($absolute)) {
                if (@unlink($absolute)) {
                    $removed++;
                    $this->pruneEmptyDirectories(dirname($absolute));
                }
            }
        }
        return $removed;
    }

    private function normalizeArchivePath(string $path): ?string
    {
        $path = str_replace('\\', '/', trim($path));
        $path = ltrim($path, '/');
        if ($path === '' || strpos($path, '../') !== false) {
            return null;
        }
        return $path;
    }

    private function pruneEmptyDirectories(string $directory): void
    {
        $moduleRoot = rtrim(str_replace('\\', '/', $this->moduleDir), '/');
        $current = rtrim(str_replace('\\', '/', $directory), '/');
        while ($current !== '' && $current !== $moduleRoot && strpos($current, $moduleRoot . '/') === 0) {
            if (!is_dir($current)) {
                $current = dirname($current);
                continue;
            }
            $entries = array_diff(scandir($current) ?: [], ['.', '..']);
            if (!empty($entries)) {
                break;
            }
            @rmdir($current);
            $current = dirname($current);
        }
    }
}
