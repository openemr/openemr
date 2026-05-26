<?php

/**
 * Guard against running OpenEMR CLI scripts as root.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2026 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Common\Command;

use RuntimeException;
use Symfony\Component\Process\Exception\ExceptionInterface as ProcessExceptionInterface;
use Symfony\Component\Process\Process;

/**
 * Refuses to let an OpenEMR CLI process start when invoked as root.
 *
 * Running PHP as root from the command line produces files (cache
 * directories, generated keys, Smarty compile output, etc.) owned by
 * UID 0 with restrictive permissions. The web server, running as a
 * non-root user, then cannot read or write them, and the resulting
 * failure surfaces far from the cause — an opaque
 * `PHP Fatal error: ... unable to write to compile_dir` deep in
 * template rendering, or `Key path … is not readable` in OAuth.
 *
 * No OpenEMR CLI script needs root. Sites that run their web server
 * as root have a configuration problem of their own — accommodating
 * them here would only hide it.
 *
 * The check is intentionally narrow:
 *
 * - CLI only (PHP_SAPI === 'cli'). Web requests are unaffected.
 * - POSIX only. Windows uses ACLs, not UID-based permissions, and the
 *   failure mode this guards against does not naturally exist there.
 * - UID 0 only. The "wrong non-root user" case is the domain of
 *   WebUserGuard, which compares against the web user's UID at
 *   per-write call sites.
 */
final class RootCliGuard
{
    /**
     * Throw if the current process is a CLI invocation running as root.
     *
     * Safe to call from any bootstrap path: no-ops on web requests, on
     * Windows, and when the current UID cannot be resolved.
     */
    public static function assertNotRoot(): void
    {
        if (PHP_SAPI !== 'cli') {
            return;
        }
        if (PHP_OS_FAMILY === 'Windows') {
            return;
        }
        $uid = self::currentEffectiveUid();
        if ($uid !== 0) {
            return;
        }

        throw new RuntimeException(
            "OpenEMR CLI scripts must not be run as root (UID 0). Doing "
            . "so creates files the web server cannot read, leading to "
            . "opaque fatal errors later (unable to write to compile_dir, "
            . "key path not readable, etc). Re-run as the web user "
            . "(typically 'apache' or 'www-data') — e.g. "
            . "`su -s /bin/sh apache -c '[your command]'`."
        );
    }

    /**
     * Resolve the current process's effective UID without requiring the
     * `posix` PHP extension.
     *
     * Prefers `posix_geteuid()`. Falls back to running `id -u` via
     * Symfony Process when posix is unavailable on a non-Windows host
     * (common in stripped-down PHP CLI builds). Returns null when
     * neither resolution path works.
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
            return null;
        }
        return null;
    }
}
