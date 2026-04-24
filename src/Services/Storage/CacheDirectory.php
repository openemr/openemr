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

use InvalidArgumentException;
use RuntimeException;

/**
 * Provides validated local cache directories for application components.
 *
 * This class manages cache directories used by the application and libraries.
 * It ensures directories are safe to use by guarding against CWE-377 (Insecure
 * Temporary File) attacks:
 *
 * - Symlinks are rejected (prevents redirect attacks)
 * - Insecure permissions are rejected (prevents tampering by other users)
 * - New directories are created with 0700 permissions (owner-only access)
 *
 * Usage:
 *
 *     $cache = new CacheDirectory();
 *     $tool->setCacheDirectory($cache->for('toolName'));
 *
 * The for() method is idempotent: calling it multiple times with the same
 * scope returns the same path and performs validation each time. This allows
 * callers to use it directly without caching the result or checking whether
 * the directory was already created.
 *
 * Note: when possible, prefer to use the Flysystem-based tooling instead of
 * this, such as the ManagerInterface in this namespace. Only use this when
 * a tool doesn't support the abstraction (typical in libraries). New, first-
 * party code should not rely on this.
 */
final readonly class CacheDirectory
{
    private string $baseDir;

    /**
     * @param string $baseDir For testing only. This parameter is guarded and
     *                        will throw LogicException outside of PHPUnit.
     *                        Production code must use the default.
     */
    public function __construct(?string $baseDir = null)
    {
        if ($baseDir === null) {
            $baseDir = sys_get_temp_dir();
        } else {
            if (!defined('PHPUNIT_COMPOSER_INSTALL')) {
                // @codeCoverageIgnoreStart
                throw new \LogicException(
                    'CacheDirectory $baseDir parameter is for testing only.'
                );
                // @codeCoverageIgnoreEnd
            }
        }
        $this->baseDir = $baseDir;
    }

    /**
     * Returns a validated cache directory path for the given scope.
     *
     * If the directory does not exist, it is created with 0700 permissions.
     * If it exists, it is validated for security (no symlinks, restrictive
     * permissions).
     *
     * @param string $scope Identifier for the cache (e.g. the tool name)
     * @return string Absolute path to the cache directory
     * @throws RuntimeException If the directory fails security validation
     */
    public function for(string $scope): string
    {
        if (preg_match('/^[a-zA-Z0-9_-]+$/', $scope) !== 1) {
            throw new InvalidArgumentException('Scope must contain only alphanumeric characters, hyphens, and underscores');
        }

        $path = $this->baseDir . '/' . $scope;

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
            // @codeCoverageIgnoreStart
            throw new RuntimeException(sprintf(
                'Cannot read permissions for cache directory: %s',
                $path,
            ));
            // @codeCoverageIgnoreEnd
        }

        if (($perms & 0777) !== 0700) {
            throw new RuntimeException(sprintf(
                'Cache directory must have 0700 permissions: %s',
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
