<?php
/**
 * MedEx Module Update Manager
 *
 * Handles version checking, update notifications, and automatic updates
 * Supports critical patch push from MedEx Admin
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    MedEx <support@MedExBank.com>
 * @copyright Copyright (c) 2018-2025 MedEx
 * @license   Proprietary - All Rights Reserved
 */

namespace OpenEMR\Modules\MedEx;

use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Common\Database\SqlQueryException;
use OpenEMR\Core\OEGlobalsBag;

class UpdateManager
{
    const CURRENT_VERSION = '1.1.0';
    const UPDATE_CHECK_INTERVAL = 3600; // Check every hour
    const CACHE_TABLE = 'medex_prefs';

    // Update priority levels
    const PRIORITY_CRITICAL = 'CRITICAL';   // Force update, security vulnerability
    const PRIORITY_SECURITY = 'SECURITY';   // Strongly recommended, security improvement
    const PRIORITY_IMPORTANT = 'IMPORTANT'; // Bug fixes, recommended
    const PRIORITY_OPTIONAL = 'OPTIONAL';   // New features, optional

    private MedExAPI $api;
    private string $moduleDir;
    private ?string $lastError = null;
    private OEGlobalsBag $globalsBag;
    private ?string $resolvedVersion = null;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->globalsBag = OEGlobalsBag::getInstance();
        $this->api = new MedExAPI();
        $this->moduleDir = realpath(__DIR__ . '/..');
    }

    /**
     * Check if updates are available
     * Returns null if check fails, array with update info if available
     */
    public function checkForUpdates(bool $forceCheck = false): ?array
    {
        $currentVersion = $this->getCurrentVersion();

        // Never ping servers if the practice hasn't registered/configured MedEx
        if (!$this->api->isConfigured()) {
            return null;
        }

        // Check cache first unless force check
        if (!$forceCheck) {
            $cached = $this->getCachedUpdateInfo();
            if ($cached !== null) {
                return $cached;
            }
        }

        // Query MedEx API for latest version
        try {
            $response = $this->api->makeRequest('index.php?route=api/oemr/module_version', [
                'module' => 'medex',
                'current_version' => $currentVersion,
                'openemr_version' => $this->globalsBag->get('v_realpatch') ?? 'unknown'
            ], 'GET');

            if (empty($response['success'])) {
                $this->lastError = $response['error'] ?? 'Failed to check for updates';
                return null;
            }

            $updateInfo = [
                'update_available' => version_compare($response['latest_version'], $currentVersion, '>'),
                'current_version' => $currentVersion,
                'latest_version' => $response['latest_version'] ?? $currentVersion,
                'priority' => $response['priority'] ?? self::PRIORITY_OPTIONAL,
                'release_date' => $response['release_date'] ?? null,
                'download_url' => $response['download_url'] ?? null,
                'changelog' => $response['changelog'] ?? '',
                'requires_manual_steps' => $response['requires_manual_steps'] ?? false,
                'manual_steps' => $response['manual_steps'] ?? '',
                'critical_message' => $response['critical_message'] ?? null,
                'min_openemr_version' => $response['min_openemr_version'] ?? null,
                'checked_at' => time()
            ];

            // Cache the result
            $this->cacheUpdateInfo($updateInfo);

            return $updateInfo;

        } catch (\Exception $e) {
            $this->lastError = 'Update check failed: ' . $e->getMessage();
            error_log('[MedEx Update] Check failed: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Get cached update info if still valid
     */
    private function getCachedUpdateInfo(): ?array
    {
        // Check if cache columns exist first
        try {
            $columns = QueryUtils::fetchRecords("SHOW COLUMNS FROM medex_prefs LIKE 'module_update_cache'");
            if (empty($columns)) {
                // Columns don't exist yet, return null (will trigger fresh check)
                return null;
            }
        } catch (\Exception $e) {
            // Table or column check failed
            return null;
        }

        try {
            $records = QueryUtils::fetchRecords(
                "SELECT module_update_cache, module_update_checked FROM medex_prefs LIMIT 1"
            );

            $result = $records[0] ?? null;

            if (!$result || empty($result['module_update_cache'])) {
                return null;
            }

            $checkedAt = strtotime($result['module_update_checked'] ?? 0);
            $cacheAge = time() - $checkedAt;

            // Cache expires after UPDATE_CHECK_INTERVAL
            if ($cacheAge > self::UPDATE_CHECK_INTERVAL) {
                return null;
            }

            $cached = json_decode($result['module_update_cache'], true);

            // If cached info shows critical update, always recheck to ensure freshness
            if (!empty($cached['priority']) && $cached['priority'] === self::PRIORITY_CRITICAL) {
                return null;
            }

            return $cached;
        } catch (\Exception $e) {
            // If query fails, just return null
            error_log('[MedEx Update] Cache read failed: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Cache update info in database
     */
    private function cacheUpdateInfo(array $info): void
    {
        try {
            // Check if medex_prefs table has the update cache columns
            $cols = QueryUtils::fetchRecords("SHOW COLUMNS FROM medex_prefs LIKE 'module_update_cache'");
            $columns = $cols[0] ?? null;

            if (!$columns) {
                // Add columns if they don't exist
                QueryUtils::sqlStatementThrowException("ALTER TABLE medex_prefs
                    ADD COLUMN module_update_cache TEXT NULL,
                    ADD COLUMN module_update_checked DATETIME NULL");
            }

            QueryUtils::sqlStatementThrowException(
                "UPDATE medex_prefs SET
                    module_update_cache = ?,
                    module_update_checked = NOW()
                WHERE 1",
                [json_encode($info)]
            );
        } catch (\Exception $e) {
            // Silently fail cache write - not critical
            error_log('[MedEx Update] Cache write failed: ' . $e->getMessage());
        }
    }

    /**
     * Check if write permissions exist for module directory
     */
    public function hasWritePermissions(): bool
    {
        return is_writable($this->moduleDir);
    }

    /**
     * Download and install update
     *
     * @param string $downloadUrl URL to download update package
     * @param bool $createBackup Whether to backup current version first
     * @return array Result with success status and message
     */
    public function installUpdate(string $downloadUrl, bool $createBackup = true): array
    {
        $steps = [];

        // Verify admin permissions
        if (!$this->api->isConfigured()) {
            return [
                'success' => false,
                'error' => 'MedEx not configured',
                'failed_step' => 'preflight',
                'steps' => $steps
            ];
        }

        // Check write permissions
        if (!$this->hasWritePermissions()) {
            return [
                'success' => false,
                'error' => 'Insufficient write permissions. Module directory is not writable.',
                'directory' => $this->moduleDir,
                'failed_step' => 'preflight',
                'steps' => $steps
            ];
        }

        try {
            // Step 1: Create backup if requested
            if ($createBackup) {
                $backupResult = $this->createBackup();
                if (!$backupResult['success']) {
                    return [
                        'success' => false,
                        'error' => (string)($backupResult['error'] ?? 'Backup failed'),
                        'failed_step' => 'backup',
                        'steps' => $steps
                    ];
                }
                $steps[] = [
                    'key' => 'backup',
                    'label' => 'Backup created',
                    'status' => 'ok'
                ];
            } else {
                $steps[] = [
                    'key' => 'backup',
                    'label' => 'Backup skipped',
                    'status' => 'ok'
                ];
            }

            // Step 2: Download update package
            $tempFile = $this->downloadUpdate($downloadUrl);
            if (!$tempFile) {
                return [
                    'success' => false,
                    'error' => 'Failed to download update package',
                    'failed_step' => 'download',
                    'steps' => $steps
                ];
            }
            $steps[] = [
                'key' => 'download',
                'label' => 'Update package downloaded',
                'status' => 'ok'
            ];

            // Step 3: Verify package integrity
            if (!$this->verifyPackage($tempFile)) {
                unlink($tempFile);
                return [
                    'success' => false,
                    'error' => 'Update package verification failed',
                    'failed_step' => 'verify',
                    'steps' => $steps
                ];
            }
            $steps[] = [
                'key' => 'verify',
                'label' => 'Update package verified',
                'status' => 'ok'
            ];

            // Step 4: Extract and install
            $installResult = $this->extractAndInstall($tempFile);
            unlink($tempFile);

            if (!$installResult['success']) {
                // Restore backup if installation failed
                if ($createBackup && !empty($backupResult['backup_file'])) {
                    $this->restoreBackup($backupResult['backup_file']);
                }
                return [
                    'success' => false,
                    'error' => (string)($installResult['error'] ?? 'Install failed'),
                    'failed_step' => 'install',
                    'steps' => $steps
                ];
            }
            $steps[] = [
                'key' => 'install',
                'label' => 'Files installed',
                'status' => 'ok'
            ];

            // Step 5: Run database migrations if needed
            $this->runMigrations($installResult['new_version'] ?? null);
            $steps[] = [
                'key' => 'migrate',
                'label' => 'Database migrations completed',
                'status' => 'ok'
            ];

            // Step 6: Clear update cache
            $this->clearUpdateCache();
            $steps[] = [
                'key' => 'cache',
                'label' => 'Update cache cleared',
                'status' => 'ok'
            ];

            return [
                'success' => true,
                'message' => 'Update installed successfully',
                'new_version' => $installResult['new_version'] ?? 'unknown',
                'backup_file' => $backupResult['backup_file'] ?? null,
                'steps' => $steps
            ];

        } catch (\Exception $e) {
            error_log('[MedEx Update] Installation failed: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'failed_step' => 'exception',
                'steps' => $steps
            ];
        }
    }

    /**
     * Create backup of current module version
     */
    public function createBackup(): array
    {
        $backupDir = $this->globalsBag->get('OE_SITE_DIR') . '/documents/medex_backups';

        // Create backup directory if it doesn't exist
        if (!is_dir($backupDir)) {
            if (!mkdir($backupDir, 0755, true)) {
                return ['success' => false, 'error' => 'Failed to create backup directory'];
            }
        }

        $timestamp = date('Y-m-d_H-i-s');
        $backupFile = $backupDir . '/medex_v' . $this->getCurrentVersion() . '_' . $timestamp . '.zip';

        try {
            $zip = new \ZipArchive();
            if ($zip->open($backupFile, \ZipArchive::CREATE) !== true) {
                return ['success' => false, 'error' => 'Failed to create backup zip file'];
            }

            // Add all module files to zip
            $this->addDirectoryToZip($zip, $this->moduleDir, 'oe-module-medex');
            $zip->close();

            error_log('[MedEx Update] Backup created: ' . $backupFile);

            return [
                'success' => true,
                'backup_file' => $backupFile,
                'backup_size' => filesize($backupFile)
            ];

        } catch (\Exception $e) {
            return ['success' => false, 'error' => 'Backup failed: ' . $e->getMessage()];
        }
    }

    /**
     * Recursively add directory to zip archive
     */
    private function addDirectoryToZip(\ZipArchive $zip, string $dir, string $zipPath): void
    {
        $files = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($dir),
            \RecursiveIteratorIterator::LEAVES_ONLY
        );

        foreach ($files as $file) {
            if (!$file->isDir()) {
                $filePath = $file->getRealPath();
                $relativePath = $zipPath . '/' . substr($filePath, strlen($dir) + 1);
                $zip->addFile($filePath, $relativePath);
            }
        }
    }

    /**
     * Download update package from MedEx server using oeHttp
     */
    private function downloadUpdate(string $url): ?string
    {
        $tempFile = tempnam(sys_get_temp_dir(), 'medex_update_');

        try {
            // Rewrite download URL to use the configured base URL (handles internal k8s routing)
            $parsedUrl = parse_url($url);
            $pathAndQuery = ($parsedUrl['path'] ?? '') . (isset($parsedUrl['query']) ? '?' . $parsedUrl['query'] : '');
            // Extract the route portion (everything from index.php onward)
            if (preg_match('#(index\.php\?.*)$#', $pathAndQuery, $m)) {
                $url = rtrim($this->api->getBaseUrl(), '/') . '/' . $m[1];
            }

            // Append session token for authentication
            $sessionToken = $this->api->getSessionToken();
            $separator = (strpos($url, '?') !== false) ? '&' : '?';
            $authenticatedUrl = $url . $separator . 'token=' . urlencode($sessionToken);

            // Use oeHttp for authenticated download
            $response = \OpenEMR\Common\Http\oeHttp::setOptions([
                'timeout' => 300, // 5 minute timeout
                'verify' => false,
                'allow_redirects' => true,
                'http_errors' => false
            ])->get($authenticatedUrl);

            $httpCode = $response->getStatusCode();
            $data = $response->getBody();

            if ($httpCode !== 200 || !$data) {
                error_log('[MedEx Update] Download failed. HTTP code: ' . $httpCode);
                return null;
            }
        } catch (\Exception $e) {
            error_log('[MedEx Update] Download error: ' . $e->getMessage());
            return null;
        }

        file_put_contents($tempFile, $data);
        return $tempFile;
    }

    /**
     * Verify downloaded package integrity
     */
    private function verifyPackage(string $file): bool
    {
        // Check if it's a valid zip file
        $zip = new \ZipArchive();
        if ($zip->open($file) !== true) {
            error_log('[MedEx Update] Invalid zip file');
            return false;
        }

        // Check for required files
        $requiredFiles = [
            'oe-module-medex/openemr.bootstrap.php',
            'oe-module-medex/moduleConfig.php'
        ];

        foreach ($requiredFiles as $required) {
            if ($zip->locateName($required) === false) {
                error_log('[MedEx Update] Missing required file: ' . $required);
                $zip->close();
                return false;
            }
        }

        $zip->close();
        return true;
    }

    /**
     * Extract and install update files
     */
    private function extractAndInstall(string $zipFile): array
    {
        $zip = new \ZipArchive();
        if ($zip->open($zipFile) !== true) {
            return ['success' => false, 'error' => 'Failed to open update package'];
        }

        // Extract to parent directory (overwrites current module files)
        $parentDir = dirname($this->moduleDir);

        if (!$zip->extractTo($parentDir)) {
            $zip->close();
            return ['success' => false, 'error' => 'Failed to extract update files'];
        }

        $zip->close();

        // Read new version from extracted bootstrap file
        $bootstrapFile = $this->moduleDir . '/openemr.bootstrap.php';
        $content = file_get_contents($bootstrapFile);

        if (preg_match("/const MODULE_VERSION = '([^']+)'/", $content, $matches)) {
            $newVersion = $matches[1];
        } else {
            $newVersion = 'unknown';
        }

        error_log('[MedEx Update] Extracted to ' . $parentDir . ', new version: ' . $newVersion);

        return [
            'success' => true,
            'new_version' => $newVersion
        ];
    }

    /**
     * Run database migrations for new version
     *
     * Public so the admin dashboard can trigger migrations on demand.
     */
    public function runMigrations(?string $newVersion = null): void
    {
        // Check if migrations directory exists
        $migrationsDir = $this->moduleDir . '/migrations';
        if (!is_dir($migrationsDir)) {
            return;
        }

        // Ensure medex_migrations table exists (avoids chicken-and-egg problem
        // where migration 001 creates the table but we need it to track migrations)
        try {
            QueryUtils::sqlStatementThrowException(
                "CREATE TABLE IF NOT EXISTS medex_migrations (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    migration_name VARCHAR(255) NOT NULL UNIQUE,
                    applied_at DATETIME NOT NULL,
                    INDEX idx_migration_name (migration_name)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
            );
        } catch (\Exception $e) {
            error_log('[MedEx Update] Failed to ensure migrations table: ' . $e->getMessage());
            throw $e;
        }

        // Get list of migration files (only .php)
        $migrations = glob($migrationsDir . '/*.php');
        sort($migrations);

        foreach ($migrations as $migration) {
            $migrationName = basename($migration, '.php');

            // Check if migration has already been run
            try {
                $result = QueryUtils::fetchRecords(
                    "SELECT migration_name FROM medex_migrations WHERE migration_name = ?",
                    [$migrationName]
                );
                if ($result) {
                    continue; // Already run
                }
            } catch (\Exception $e) {
                // Table might not exist yet (should not happen after CREATE above)
                error_log('[MedEx Update] Migration check failed: ' . $e->getMessage());
            }

            // Run migration
            try {
                require_once $migration;

                // Record migration as completed
                QueryUtils::sqlStatementThrowException(
                    "INSERT INTO medex_migrations (migration_name, applied_at) VALUES (?, NOW())",
                    [$migrationName]
                );

                error_log('[MedEx Update] Migration completed: ' . $migrationName);

            } catch (\Exception $e) {
                error_log('[MedEx Update] Migration failed: ' . $migrationName . ' - ' . $e->getMessage());
                throw $e;
            }
        }
    }

    /**
     * Restore from backup
     */
    private function restoreBackup(string $backupFile): bool
    {
        if (!file_exists($backupFile)) {
            return false;
        }

        $zip = new \ZipArchive();
        if ($zip->open($backupFile) !== true) {
            return false;
        }

        $parentDir = dirname($this->moduleDir);
        $result = $zip->extractTo($parentDir);
        $zip->close();

        error_log('[MedEx Update] Restored from backup: ' . $backupFile);

        return $result;
    }

    /**
     * Clear update cache to force fresh check
     */
    private function clearUpdateCache(): void
    {
        QueryUtils::sqlStatementThrowException(
            "UPDATE medex_prefs SET
                module_update_cache = NULL,
                module_update_checked = NULL
            WHERE 1"
        );
    }

    /**
     * Get last error message
     */
    public function getLastError(): ?string
    {
        return $this->lastError;
    }

    /**
     * Check if critical update is required
     * This is called on every page load to enforce critical patches
     */
    public static function checkCriticalUpdate(): ?array
    {
        $manager = new self();
        $updateInfo = $manager->checkForUpdates();

        if ($updateInfo &&
            $updateInfo['update_available'] &&
            $updateInfo['priority'] === self::PRIORITY_CRITICAL) {
            return $updateInfo;
        }

        return null;
    }

    /**
     * Get list of available backups
     *
     * @return array List of backup files with metadata
     */
    public function getBackups(): array
    {
        $backupDir = $this->globalsBag->get('OE_SITE_DIR') . '/documents/medex_backups';

        if (!is_dir($backupDir)) {
            return [];
        }

        $backups = [];
        $files = glob($backupDir . '/medex_v*.zip');

        foreach ($files as $file) {
            $filename = basename($file);

            // Parse version and timestamp from filename
            // Format: medex_v1.0.0_2025-01-22_14-30-45.zip
            if (preg_match('/medex_v([^_]+)_(.+)\.zip$/', $filename, $matches)) {
                $version = $matches[1];
                $timestamp = str_replace(['_', '-'], [' ', ':'], $matches[2]);
                $timestamp = substr($timestamp, 0, 10) . ' ' . substr($timestamp, 11);

                $backups[] = [
                    'file' => $file,
                    'filename' => $filename,
                    'version' => $version,
                    'timestamp' => $timestamp,
                    'date' => strtotime($timestamp),
                    'size' => filesize($file),
                    'size_mb' => round(filesize($file) / 1024 / 1024, 2)
                ];
            }
        }

        // Sort by date descending (newest first)
        usort($backups, function ($a, $b) {
            return $b['date'] - $a['date'];
        });

        return $backups;
    }

    /**
     * Rollback to a previous version from backup
     *
     * @param string $backupFile Path to backup ZIP file
     * @param bool $createCurrentBackup Whether to backup current version first
     * @return array Result with success status
     */
    public function rollback(string $backupFile, bool $createCurrentBackup = true): array
    {
        if (!file_exists($backupFile)) {
            return ['success' => false, 'error' => 'Backup file not found'];
        }

        if (!$this->hasWritePermissions()) {
            return [
                'success' => false,
                'error' => 'Insufficient write permissions',
                'directory' => $this->moduleDir
            ];
        }

        try {
            // Step 1: Backup current version before rollback
            $currentBackup = null;
            if ($createCurrentBackup) {
                $backupResult = $this->createBackup();
                if (!$backupResult['success']) {
                    return [
                        'success' => false,
                        'error' => 'Failed to backup current version before rollback: ' . $backupResult['error']
                    ];
                }
                $currentBackup = $backupResult['backup_file'];
                error_log('[MedEx Rollback] Current version backed up to: ' . $currentBackup);
            }

            // Step 2: Extract backup
            $success = $this->restoreBackup($backupFile);

            if (!$success) {
                return [
                    'success' => false,
                    'error' => 'Failed to restore from backup',
                    'backup_file' => $backupFile
                ];
            }

            // Step 3: Read rolled-back version
            $bootstrapFile = $this->moduleDir . '/openemr.bootstrap.php';
            $content = file_get_contents($bootstrapFile);
            $rolledBackVersion = 'unknown';

            if (preg_match("/const MODULE_VERSION = '([^']+)'/", $content, $matches)) {
                $rolledBackVersion = $matches[1];
            }

            // Step 4: Clear update cache
            $this->clearUpdateCache();

            error_log('[MedEx Rollback] Successfully rolled back to version: ' . $rolledBackVersion);

            return [
                'success' => true,
                'message' => 'Successfully rolled back to previous version',
                'rolled_back_version' => $rolledBackVersion,
                'backup_file' => $backupFile,
                'current_backup' => $currentBackup
            ];

        } catch (\Exception $e) {
            error_log('[MedEx Rollback] Failed: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => 'Rollback failed: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Delete a backup file
     *
     * @param string $backupFile Path to backup file
     * @return bool Success status
     */
    public function deleteBackup(string $backupFile): bool
    {
        if (!file_exists($backupFile)) {
            return false;
        }

        // Verify it's in the backups directory (security check)
        $backupDir = $this->globalsBag->get('OE_SITE_DIR') . '/documents/medex_backups';
        $realPath = realpath($backupFile);
        $realBackupDir = realpath($backupDir);

        if (strpos($realPath, $realBackupDir) !== 0) {
            error_log('[MedEx] Attempted to delete file outside backup directory: ' . $backupFile);
            return false;
        }

        return unlink($backupFile);
    }

    /**
     * Get current module version
     *
     * @return string Current version
     */
    public function getCurrentVersion(): string
    {
        if ($this->resolvedVersion !== null) {
            return $this->resolvedVersion;
        }

        $bootstrapFile = $this->moduleDir . '/openemr.bootstrap.php';
        if (is_file($bootstrapFile) && is_readable($bootstrapFile)) {
            $content = file_get_contents($bootstrapFile);
            if (is_string($content) && preg_match("/const\\s+MODULE_VERSION\\s*=\\s*'([^']+)'\\s*;/", $content, $matches)) {
                $this->resolvedVersion = $matches[1];
                return $this->resolvedVersion;
            }
        }

        $this->resolvedVersion = self::CURRENT_VERSION;
        return $this->resolvedVersion;
    }

    /**
     * Get module directory path
     *
     * @return string Module directory path
     */
    public function getModuleDir(): string
    {
        return $this->moduleDir;
    }
}
