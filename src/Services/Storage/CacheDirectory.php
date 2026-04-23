<?php

/**
 * Secure local cache directory management.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Eric Stern <erics@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR <https://opencoreemr.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Services\Storage;

use RuntimeException;

/**
 * Provides validated local cache directories for application components.
 *
 * This class manages cache directories used by libraries like Smarty (template
 * compilation) and mPDF (font caching). It ensures directories are safe to use
 * by guarding against CWE-377 (Insecure Temporary File) attacks:
 *
 * - Symlinks are rejected (prevents redirect attacks)
 * - Insecure permissions are rejected (prevents tampering by other users)
 * - New directories are created with 0700 permissions (owner-only access)
 *
 * Usage:
 *
 *     $cache = new CacheDirectory();
 *     $smarty->setCompileDir($cache->for('smarty'));
 *     $mpdfConfig['tempDir'] = $cache->for('mpdf');
 *
 * The for() method is idempotent: calling it multiple times with the same
 * scope returns the same path and performs validation each time. This allows
 * callers to use it directly without caching the result or checking whether
 * the directory was already created.
 */
final class CacheDirectory
{
    public function __construct(
        private readonly string $baseDir = '',
    ) {
    }

    /**
     * Returns a validated cache directory path for the given scope.
     *
     * If the directory does not exist, it is created with 0700 permissions.
     * If it exists, it is validated for security (no symlinks, restrictive
     * permissions).
     *
     * @param string $scope Identifier for the cache (e.g., 'smarty', 'mpdf')
     * @return string Absolute path to the cache directory
     * @throws RuntimeException If the directory fails security validation
     */
    public function for(string $scope): string
    {
        $baseDir = $this->baseDir !== '' ? $this->baseDir : sys_get_temp_dir();
        $path = $baseDir . '/' . $scope;

        if (is_link($path)) {
            throw new RuntimeException(sprintf(
                'Cache directory must not be a symlink: %s',
                $path,
            ));
        }

        if (is_dir($path)) {
            $this->validatePermissions($path);
            return $path;
        }

        $this->createDirectory($path);
        return $path;
    }

    private function validatePermissions(string $path): void
    {
        $perms = fileperms($path);
        if ($perms === false) {
            throw new RuntimeException(sprintf(
                'Cannot read permissions for cache directory: %s',
                $path,
            ));
        }

        if ($perms & 0022) {
            throw new RuntimeException(sprintf(
                'Cache directory has insecure permissions (group or world writable): %s',
                $path,
            ));
        }
    }

    private function createDirectory(string $path): void
    {
        $oldUmask = umask(0077);
        try {
            if (!mkdir($path, 0700, true)) {
                throw new RuntimeException(sprintf(
                    'Failed to create cache directory: %s',
                    $path,
                ));
            }
        } finally {
            umask($oldUmask);
        }
    }
}
