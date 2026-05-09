<?php

/**
 * Build (or validate) the on-disk directory the OIDC session refresh
 * handler uses for its PSR-16 filesystem cache (JWKS documents and
 * OIDC discovery metadata).
 *
 * Aisle round-3 finding #6 (CWE-377). The previous inline
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
 * Aisle round-4 finding #3 (CWE-367) closed a TOCTOU window in the
 * round-3 fix itself: a separate `is_link()` pre-check followed by an
 * `is_dir()` post-check left a race window where an attacker could
 * inject a symlink between the two operations. `is_dir()` follows
 * symlinks, so the symlink would slip through. The current
 * implementation instead validates the post-state via `lstat()`,
 * which does NOT follow symlinks — there is no second check to race.
 *
 * This factory hardens the creation:
 *
 *   - canonicalize the base via `realpath()` so a pathological
 *     `temporary_files_dir = /tmp/../etc` trick can't escape;
 *   - call `mkdir()` once, then `lstat()` the result and demand it
 *     be a real directory (S_IFDIR), refusing symlinks/files/fifos;
 *   - use `0700` so only the PHP process user can access entries;
 *   - handle the concurrent-create race naturally — `lstat()` of an
 *     already-created directory still returns S_IFDIR, so a benign
 *     race is indistinguishable from a clean create;
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

    /** POSIX `S_IFMT` mask — extracts the file-type nibble from `stat['mode']`. */
    private const STAT_MODE_TYPE_MASK = 0o170000;

    /** POSIX `S_IFDIR` — value of the file-type nibble for a real directory. */
    private const STAT_MODE_DIRECTORY = 0o040000;

    /**
     * Validate (or create) the cache directory under `$baseTempDir`.
     * Returns the absolute, canonicalized path on success.
     *
     * @return non-empty-string The validated cache directory path.
     * @throws RuntimeException When the base doesn't resolve, the
     *   target turns out to be a symlink (or any non-directory
     *   dirent), or `mkdir` fails for a non-recoverable reason.
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

        // Single mkdir attempt. Failure is not fatal here — the path
        // may already exist from a concurrent refresh (benign race),
        // or from a hostile pre-create (lstat below catches it). We
        // intentionally do NOT pass `recursive: true`: `realpath()`
        // above already canonicalized and verified the parent, so
        // there are no intermediate dirs to create, and recursive
        // mkdir has fiddly partial-failure semantics we don't need.
        @mkdir($cacheDir, self::DIRECTORY_PERMISSIONS);

        // Round-4 #3 (CWE-367) — TOCTOU defense. The previous shape
        // of this method had `is_link()` *before* mkdir and `is_dir()`
        // *after*, leaving a race window: an attacker could create a
        // symlink between the two checks, mkdir would EEXIST, and
        // `is_dir()` would follow the symlink and return true. We
        // now use `lstat()` which does NOT follow symlinks and tells
        // us the actual dirent type — no second check, no race.
        $stat = @lstat($cacheDir);
        if ($stat === false) {
            throw new RuntimeException(
                'Failed to create oidc_cache directory: ' . $cacheDir,
            );
        }

        if (($stat['mode'] & self::STAT_MODE_TYPE_MASK) !== self::STAT_MODE_DIRECTORY) {
            throw new RuntimeException(
                'oidc_cache path exists but is not a real directory '
                . '(possibly a symlink, regular file, or other dirent): '
                . $cacheDir,
            );
        }

        return $cacheDir;
    }
}
