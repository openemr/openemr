<?php

/**
 * Resolve include paths safely, rejecting traversal and other attacks.
 *
 * @package   OpenEMR
 *
 * @link      https://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Common\Filesystem;

/**
 * Resolve a file path relative to a base directory, ensuring the result
 * is a regular file that stays within the base directory.
 *
 * Rejects `.`, `..`, NUL bytes, and paths that resolve outside the base
 * directory via symlinks or traversal sequences.
 */
final class SafeIncludeResolver
{
    /**
     * Resolve a relative path under a base directory.
     *
     * @param string $baseDir Absolute path to the trusted base directory
     * @param string $relativePath Untrusted relative path (e.g. "formdir/report.php")
     * @return string|false The resolved real path, or false if validation fails
     */
    public static function resolve(string $baseDir, string $relativePath): string|false
    {
        $realBaseDir = realpath($baseDir);
        if ($realBaseDir === false) {
            return false;
        }

        if (self::containsUnsafeSegment($relativePath)) {
            return false;
        }

        try {
            $realPath = realpath($realBaseDir . DIRECTORY_SEPARATOR . $relativePath);
        } catch (\ValueError) {
            return false;
        }

        if ($realPath === false) {
            return false;
        }

        if (!is_file($realPath)) {
            return false;
        }

        if (!str_starts_with($realPath, $realBaseDir . DIRECTORY_SEPARATOR)) {
            return false;
        }

        return $realPath;
    }

    /**
     * Check whether a path component is safe to use in an include path.
     *
     * Use this for early rejection of a single directory name (e.g. formdir
     * from a database) before building the full relative path.
     *
     * @param mixed $component The value to check (typically from a DB row)
     * @return bool True if the component is a safe non-empty string
     */
    public static function isSafePathComponent(mixed $component): bool
    {
        if (!is_string($component)) {
            return false;
        }

        if (in_array($component, ['', '.', '..'], true)) {
            return false;
        }

        if (str_contains($component, "\0")) {
            return false;
        }

        if (str_contains($component, '/') || str_contains($component, '\\')) {
            return false;
        }

        return true;
    }

    private static function containsUnsafeSegment(string $path): bool
    {
        if (str_contains($path, "\0")) {
            return true;
        }

        if (str_contains($path, '://')) {
            return true;
        }

        // Check each path segment for . or ..
        $segments = preg_split('#[/\\\\]#', $path);
        if (!is_array($segments)) {
            return true;
        }

        foreach ($segments as $segment) {
            if ($segment === '.' || $segment === '..') {
                return true;
            }
        }

        return false;
    }
}
