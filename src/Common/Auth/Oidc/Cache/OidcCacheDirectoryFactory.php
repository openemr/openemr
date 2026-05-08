<?php

/**
 * Build (or validate) the on-disk directory the OIDC session refresh
 * handler uses for its PSR-16 filesystem cache (JWKS documents and
 * OIDC discovery metadata).
 *
 * Aisle finding #6 (CWE-377). The previous inline
 * `is_dir() + mkdir(0o755)` pattern in `oidc_session_refresh.php` was
 * vulnerable to two related issues when `temporary_files_dir` happens
 * to be a shared world-writable location like `/tmp`:
 *
 *   1. **Symlink attack.** A local attacker pre-creates the cache path
 *      as a symlink to an attacker-controlled directory. `is_dir()`
 *      follows symlinks, so the original code skipped `mkdir` and
 *      happily wrote cached JWKS / discovery documents to the symlink
 *      target — leaking security metadata to other users or letting
 *      the attacker overwrite their target file.
 *   2. **Loose permissions.** `0755` is world-readable, meaning any
 *      local user could read cached JWKS contents.
 *
 * This factory hardens the creation:
 *
 *   - canonicalize the base via `realpath()` so a pathological
 *     `temporary_files_dir = /tmp/../etc` trick can't escape;
 *   - refuse outright if the cache path is already a symlink;
 *   - use `0700` so only the PHP process user can access entries;
 *   - handle the concurrent-create race (two refresh requests may
 *     both see the dir absent and both call mkdir);
 *   - fail loudly with a `RuntimeException` carrying the offending
 *     path rather than silently continuing.
 *
 * @link      https://www.open-emr.org
 * @author    Milan Zivkovic <zivkovic.milan@gmail.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 */

declare(strict_types=1);

namespace OpenEMR\Common\Auth\Oidc\Cache;

use RuntimeException;

final readonly class OidcCacheDirectoryFactory
{
    /** Subdirectory name appended under the canonicalized base temp dir. */
    public const DIRECTORY_NAME = 'oidc_cache';

    /** Restrict permissions so only the PHP process user can read/write. */
    private const DIRECTORY_PERMISSIONS = 0o700;

    /**
     * Validate (or create) the cache directory under `$baseTempDir`.
     * Returns the absolute, canonicalized path on success.
     *
     * @return non-empty-string The validated cache directory path.
     * @throws RuntimeException When the base doesn't resolve, the
     *   target is a symlink, or `mkdir` fails for any reason that
     *   isn't a benign concurrent-create race.
     */
    public function create(string $baseTempDir): string
    {
        $baseReal = realpath($baseTempDir);
        if ($baseReal === false) {
            throw new RuntimeException(
                'temporary_files_dir does not exist or is not a directory: ' . $baseTempDir,
            );
        }

        $cacheDir = $baseReal . DIRECTORY_SEPARATOR . self::DIRECTORY_NAME;

        if (is_link($cacheDir)) {
            throw new RuntimeException(
                'oidc_cache path is a symlink — refusing to use it: ' . $cacheDir,
            );
        }

        if (!is_dir($cacheDir)) {
            // The trailing `&& !is_dir` handles the concurrent-create
            // race: two refresh requests can both see the dir absent
            // and both call mkdir; one wins, the other returns false
            // but is_dir is true after-the-fact. Treat that as success.
            // Real failures (permission denied, parent missing, etc.)
            // leave is_dir false and we throw.
            if (!mkdir($cacheDir, self::DIRECTORY_PERMISSIONS, true) && !is_dir($cacheDir)) {
                throw new RuntimeException(
                    'Failed to create oidc_cache directory: ' . $cacheDir,
                );
            }
        }

        return $cacheDir;
    }
}
