<?php

/**
 * VersionFile - the values declared by the project's version.php
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Core;

/**
 * version.php assigns its values to variables in whatever scope includes it,
 * which makes every reader dependent on being the first to include it: a
 * require_once is a no-op once another entry point (admin.php, the installer)
 * has run it, and the variables are then simply absent. This value object is
 * the scope-independent way to read those values.
 *
 * Each load re-evaluates version.php. In a dev environment that means a fresh
 * cache-busting token for {@see self::$jsIncludes} every time, so bootstrap
 * loads once and publishes the result; callers should read the published
 * values rather than loading again.
 */
final readonly class VersionFile
{
    public function __construct(
        public string $major,
        public string $minor,
        public string $patch,
        public string $tag,
        public string $realpatch,
        public int $database,
        public int $acl,
        public int|string $jsIncludes,
    ) {
    }

    /**
     * Read the version.php at the root of an OpenEMR project directory.
     */
    public static function load(string $projectDir): self
    {
        return self::fromFile($projectDir . '/version.php');
    }

    public static function fromFile(string $path): self
    {
        $values = self::evaluate($path);

        return new self(
            major: self::stringValue($values, 'v_major', $path),
            minor: self::stringValue($values, 'v_minor', $path),
            patch: self::stringValue($values, 'v_patch', $path),
            tag: self::stringValue($values, 'v_tag', $path),
            realpatch: self::stringValue($values, 'v_realpatch', $path),
            database: self::intValue($values, 'v_database', $path),
            acl: self::intValue($values, 'v_acl', $path),
            jsIncludes: self::scalarValue($values, 'v_js_includes', $path),
        );
    }

    /**
     * Evaluate the version file in an isolated scope and collect what it declared.
     *
     * The plain require is deliberate. require_once would yield nothing at all
     * whenever some other bootstrap path had already included the file, which
     * is the failure this class exists to prevent. Re-evaluating is safe:
     * version.php only assigns variables.
     *
     * @return array<string, mixed>
     */
    private static function evaluate(string $path): array
    {
        if (!is_file($path)) {
            throw new \RuntimeException('Version file not found: ' . $path);
        }

        $collect = static function (string $path): array {
            require $path;
            // Drop the parameter so only what version.php declared is returned.
            unset($path);
            return get_defined_vars();
        };

        return $collect($path);
    }

    /**
     * @param array<string, mixed> $values
     */
    private static function stringValue(array $values, string $name, string $path): string
    {
        $value = $values[$name] ?? null;
        if (is_string($value)) {
            return $value;
        }
        if (is_int($value)) {
            return (string) $value;
        }

        throw self::invalid($name, $path);
    }

    /**
     * @param array<string, mixed> $values
     */
    private static function intValue(array $values, string $name, string $path): int
    {
        $value = $values[$name] ?? null;
        if (is_int($value)) {
            return $value;
        }
        if (is_string($value) && ctype_digit($value)) {
            return (int) $value;
        }

        throw self::invalid($name, $path);
    }

    /**
     * @param array<string, mixed> $values
     */
    private static function scalarValue(array $values, string $name, string $path): int|string
    {
        $value = $values[$name] ?? null;
        if (is_int($value) || is_string($value)) {
            return $value;
        }

        throw self::invalid($name, $path);
    }

    private static function invalid(string $name, string $path): \RuntimeException
    {
        return new \RuntimeException(sprintf('%s does not declare a usable $%s', $path, $name));
    }
}
