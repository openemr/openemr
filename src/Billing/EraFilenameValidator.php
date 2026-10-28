<?php

/**
 * ERA (X12 835) filename validation and resolution.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2026 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Billing;

/**
 * Validate ERA filenames derived from parsed X12 835 content before using
 * them to build destination paths.
 *
 * The X12 element values parsed out of an uploaded 835 become the on-disk
 * filename stem. Treat those bytes as untrusted and reject anything that
 * cannot resolve to a safe regular filename inside the configured ERA
 * directory.
 */
final class EraFilenameValidator
{
    /**
     * Expected shape: `YYYYMMDD_<control-number>_<payer-id>`.
     * gs_date is strictly numeric per X12 (always 8 digits). isa_control_number
     * is numeric per X12. payer_id is alphanumeric per X12 (NAIC codes are
     * numeric but real-world payer IDs frequently include letters, e.g. `AETNA`,
     * `UHC`, `BCBSTX`).
     */
    public const FILENAME_PATTERN = '/^[0-9]{8}_[0-9A-Za-z]+_[0-9A-Za-z]+$/';

    /**
     * Return a safe `basename` for the given candidate, or null if the
     * candidate is empty, mis-shaped, or contains traversal/separator bytes.
     *
     * The caller still owns the base-directory `realpath()` check — pair this
     * with resolveWithinDirectory() when the directory is known.
     */
    public static function sanitize(string $candidate): ?string
    {
        if ($candidate === '') {
            return null;
        }
        if (preg_match(self::FILENAME_PATTERN, $candidate) !== 1) {
            return null;
        }
        $sanitized = basename($candidate);
        if ($sanitized !== $candidate) {
            return null;
        }
        return $sanitized;
    }

    /**
     * Resolve a candidate filename stem inside a trusted base directory,
     * returning the absolute file path. Returns null when the stem fails
     * validation or the computed destination escapes the base directory.
     *
     * `$suffix` is appended verbatim (e.g. `.edi`, `.835`, `.html`).
     * `$prefix` is prepended verbatim (e.g. `.pending_`).
     *
     * The directory itself must already exist; the destination file does not
     * need to exist yet (that is checked by the caller after rename/copy).
     */
    public static function resolveWithinDirectory(
        string $baseDir,
        string $candidate,
        string $suffix,
        string $prefix = ''
    ): ?string {
        $safeName = self::sanitize($candidate);
        if ($safeName === null) {
            return null;
        }
        $realBaseDir = realpath($baseDir);
        if ($realBaseDir === false) {
            return null;
        }
        $target = $realBaseDir . DIRECTORY_SEPARATOR . $prefix . $safeName . $suffix;
        $parent = dirname($target);
        $realParent = realpath($parent);
        if ($realParent === false || $realParent !== $realBaseDir) {
            return null;
        }
        return $target;
    }
}
