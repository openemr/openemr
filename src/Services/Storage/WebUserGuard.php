<?php

/**
 * Guard against creating files the web server cannot read later.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Services\Storage;

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
 * The "web user" is detected from the owner of a directory OpenEMR
 * guarantees is writable by the web server at runtime — specifically
 * `{site_dir}/documents` (where patient documents are written). Whoever
 * owns that directory IS the runtime web user, by definition; if it
 * weren't, document uploads would already be broken.
 *
 * Non-POSIX systems (Windows): the check is skipped entirely. Windows
 * uses ACLs, not UID-based permissions, and the failure mode this guards
 * against doesn't naturally exist there.
 */
final class WebUserGuard
{
    /**
     * Assert that this process can safely create files the web server
     * will read later, using the default reference directory
     * ({site_dir}/documents).
     *
     * No-ops on non-POSIX systems and when the reference directory
     * cannot be located (e.g. early in bootstrap before
     * `$GLOBALS['OE_SITE_DIR']` is set).
     *
     * @param string $writeContext Short description of what's about to be
     *                             written, included in the error message
     *                             to help the admin locate the mistake.
     */
    public static function assertSafe(string $writeContext): void
    {
        $referencePath = self::defaultReferencePath();
        if ($referencePath === null) {
            return;
        }
        self::assertSafeWithReference($writeContext, $referencePath);
    }

    /**
     * Assert that this process can safely create files the web server
     * will read later, using a caller-supplied reference directory.
     *
     * Use this overload when the caller already has a known-writable
     * directory in hand (e.g. OAuth2KeyConfig already knows
     * `{siteDir}/documents`), or in tests.
     *
     * No-ops on non-POSIX systems and when the reference path cannot be
     * stat'd.
     *
     * @param string $writeContext   Short description of what's about to
     *                               be written, included in the error
     *                               message.
     * @param string $referencePath  Path to a directory the web server
     *                               writes to at runtime; its owner IS
     *                               the web user.
     */
    public static function assertSafeWithReference(string $writeContext, string $referencePath): void
    {
        $current = self::currentEffectiveUid();
        if ($current === null) {
            // Non-POSIX (Windows), or couldn't determine — skip.
            return;
        }

        $owner = @fileowner($referencePath);
        if ($owner === false) {
            // Reference path missing/unreadable. Fail open: this is a
            // safety check, not a load-bearing precondition; if the
            // install is broken enough that the documents dir isn't
            // there, the user has bigger problems.
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
            . "`su -s /bin/sh \$(id -un %d) -c '<your command>'`.",
            $writeContext,
            $current,
            $referencePath,
            $owner,
            $owner,
            $owner,
        ));
    }

    /**
     * Resolve the current process's effective UID without requiring the
     * `posix` PHP extension to be loaded.
     *
     * Prefers `posix_geteuid()`. Falls back to running `id -u` via
     * Symfony Process when posix is unavailable (common in stripped-down
     * PHP CLI builds). Returns null on Windows / when neither path
     * works.
     */
    private static function currentEffectiveUid(): ?int
    {
        if (function_exists('posix_geteuid')) {
            return posix_geteuid();
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

    /**
     * Find the default reference directory for the current OpenEMR site.
     *
     * Returns null if the site directory isn't set in $GLOBALS (e.g.
     * pre-bootstrap call) or the documents subdirectory doesn't exist.
     */
    private static function defaultReferencePath(): ?string
    {
        // Direct $GLOBALS read intentional: this runs in low-level filesystem
        // code paths where the OEGlobalsBag kernel may not have been
        // initialized yet.
        // @phpstan-ignore openemr.forbiddenGlobalsAccess
        $siteDir = $GLOBALS['OE_SITE_DIR'] ?? null;
        if (!is_string($siteDir) || $siteDir === '') {
            return null;
        }
        $docs = $siteDir . '/documents';
        return is_dir($docs) ? $docs : null;
    }
}
