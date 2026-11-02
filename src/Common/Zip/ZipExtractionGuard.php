<?php

/**
 * Reject ZIP archives whose uncompressed footprint exceeds conservative limits.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2026 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Common\Zip;

use ZipArchive;

/**
 * Inspect an opened ZIP archive against conservative per-entry, total-size,
 * entry-count, and compression-ratio limits before extraction.
 *
 * ZIP members report their uncompressed size in the local file header, so
 * callers can decide to reject before loading any entry content into memory.
 */
final class ZipExtractionGuard
{
    /** Per-entry uncompressed byte limit. */
    public const DEFAULT_MAX_ENTRY_BYTES = 50 * 1024 * 1024;

    /** Total uncompressed byte limit across all entries. */
    public const DEFAULT_MAX_TOTAL_BYTES = 100 * 1024 * 1024;

    /** Maximum entry count. */
    public const DEFAULT_MAX_ENTRIES = 500;

    /** Reject archives whose per-entry compression ratio exceeds this. */
    public const DEFAULT_MAX_RATIO = 200.0;

    /**
     * Return true when the archive is safe to extract under the given limits.
     * Returns false on any failing check, including an unreadable stat index.
     */
    public static function isSafe(
        ZipArchive $zip,
        int $maxEntryBytes = self::DEFAULT_MAX_ENTRY_BYTES,
        int $maxTotalBytes = self::DEFAULT_MAX_TOTAL_BYTES,
        int $maxEntries = self::DEFAULT_MAX_ENTRIES,
        float $maxRatio = self::DEFAULT_MAX_RATIO,
    ): bool {
        $count = $zip->numFiles;
        if ($count < 0 || $count > $maxEntries) {
            return false;
        }
        $totalUncompressed = 0;
        for ($i = 0; $i < $count; $i++) {
            $stat = $zip->statIndex($i);
            if ($stat === false) {
                return false;
            }
            $uncompressed = (int) $stat['size'];
            $compressed = (int) $stat['comp_size'];
            if ($uncompressed < 0 || $uncompressed > $maxEntryBytes) {
                return false;
            }
            if ($compressed > 0 && ($uncompressed / $compressed) > $maxRatio) {
                return false;
            }
            $totalUncompressed += $uncompressed;
            if ($totalUncompressed > $maxTotalBytes) {
                return false;
            }
        }
        return true;
    }
}
