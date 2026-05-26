<?php

/**
 * Guard against creating files the web server cannot read later.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2026 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Services\Storage;

use OpenEMR\Core\OEGlobalsBag;
use RuntimeException;
use Symfony\Component\Process\Exception\ExceptionInterface as ProcessExceptionInterface;
use Symfony\Component\Process\Process;

/**
 * Asserts that the current PHP process is running as the same UID as the
 * web server, so files this process creates will be readable by the web
 * server later.
 *
 * The mismatch this catches: a CLI invocation (e.g. an admin's
 * `sudo php sql_upgrade.php`, or a cron job configured to run as root)
 * creates a file or directory with restrictive permissions; the web
 * server, running as a different UID, then cannot read it. The failure
 * later is opaque — a `PHP Fatal error: ... unable to write to ...`
 * deep in template rendering or PDF generation, far from the original
 * mistake.
 *
 * Surfacing the mismatch at the *write* call site converts that opaque
 * failure into an immediate, actionable error pointing at the actual
 * fix: "re-run PHP as the web user."
 *
 * The reference directory used for the comparison must be one the web
 * server is known to write to at runtime. By default the guard uses
 * `{OE_SITE_DIR}/documents` (where patient documents land); whoever
 * owns that directory IS the runtime web user, by definition (if it
 * weren't, document uploads would already be broken). Callers with a
 * different or multisite-specific path can pass it explicitly.
 *
 * Non-POSIX systems (Windows): the check is skipped entirely. Windows
 * uses ACLs, not UID-based permissions, and the failure mode this
 * guards against doesn't naturally exist there.
 */
final class WebUserGuard
{
    /**
     * Assert that this process can safely create files the web server
     * will read later.
     *
     * No-ops on non-POSIX systems and when the reference path cannot be
     * stat'd (a missing reference is a bigger install problem; this
     * helper is a safety check, not a load-bearing precondition).
     *
     * @param string      $writeContext  Short description of what's
     *                                   about to be written, included
     *                                   in the error message to help
     *                                   the admin locate the mistake.
     * @param string|null $referencePath Path to a directory the web
     *                                   server writes to at runtime;
     *                                   its owner IS the web user.
     *                                   When null, resolved from
     *                                   `OEGlobalsBag` as
     *                                   `{OE_SITE_DIR}/documents`.
     *                                   Pass explicitly when the
     *                                   caller knows the site context
     *                                   (e.g. multisite).
     */
    public static function assertSafe(string $writeContext, ?string $referencePath = null): void
    {
        $referencePath ??= self::defaultReferencePath();
        $current = self::currentEffectiveUid();
        if ($current === null) {
            // Non-POSIX (Windows), or couldn't determine — skip.
            return;
        }

        $owner = @fileowner($referencePath);
        if ($owner === false) {
            return;
        }

        if ($current === $owner) {
            return;
        }

        throw new RuntimeException(sprintf(
            "Web-user mismatch: '%s' would be done by UID %d, but '%s' "
            . "(a directory the web server writes to) is owned by UID %d. "
            . "Files this process creates would not be readable by the web "
            . "server, which would cause opaque fatal errors later "
            . "(unable to write to compile_dir, key path not readable, "
            . "etc). Re-run PHP as UID %d — e.g. via "
            . "`su -s /bin/sh \$(id -un %d) -c '[your command]'`.",
            $writeContext,
            $current,
            $referencePath,
            $owner,
            $owner,
            $owner,
        ));
    }

    /**
     * Resolve the default reference directory from the bootstrapped
     * site context.
     *
     * @throws \LogicException when OE_SITE_DIR is not available via
     *                         OEGlobalsBag. Callers reaching this path
     *                         have presumably bootstrapped OpenEMR;
     *                         not having OE_SITE_DIR is a programmer
     *                         error worth surfacing loudly rather
     *                         than silently no-opping.
     */
    private static function defaultReferencePath(): string
    {
        $siteDir = OEGlobalsBag::getInstance()->get('OE_SITE_DIR');
        if (!is_string($siteDir) || $siteDir === '') {
            throw new \LogicException(
                'WebUserGuard could not resolve a default reference path: '
                . 'OE_SITE_DIR is not set via OEGlobalsBag (call site is '
                . 'likely pre-bootstrap). Pass a $referencePath explicitly '
                . 'or load interface/globals.php first.'
            );
        }
        return $siteDir . '/documents';
    }

    /**
     * Resolve the current process's effective UID without requiring the
     * `posix` PHP extension to be loaded.
     *
     * Prefers `posix_geteuid()`. Falls back to running `id -u` via
     * Symfony Process when posix is unavailable on a non-Windows host
     * (common in stripped-down PHP CLI builds). Returns null on Windows
     * (where the comparison doesn't apply — Windows uses ACLs) and when
     * neither resolution path works.
     */
    private static function currentEffectiveUid(): ?int
    {
        if (function_exists('posix_geteuid')) {
            return posix_geteuid();
        }
        // Don't fall back to `id -u` on Windows: Git/MSYS in PATH would
        // provide an `id` binary that returns a Unix-style UID, but
        // fileowner() on Windows returns ACL-derived integers that
        // don't share the same numbering space — comparing the two
        // would produce nonsense mismatches.
        if (PHP_OS_FAMILY === 'Windows') {
            return null;
        }
        try {
            $process = new Process(['id', '-u']);
            $process->run();
            if (!$process->isSuccessful()) {
                return null;
            }
            $trimmed = trim($process->getOutput());
            if ($trimmed !== '' && ctype_digit($trimmed)) {
                return (int) $trimmed;
            }
        } catch (ProcessExceptionInterface) {
            // ProcessStartFailedException (binary missing), etc.
            return null;
        }
        return null;
    }
}
