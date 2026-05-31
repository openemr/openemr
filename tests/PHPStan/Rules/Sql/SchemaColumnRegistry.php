<?php

/**
 * Schema-derived identifier set for OpenEMR's column-collision check.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2026 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\PHPStan\Rules\Sql;

use RuntimeException;

/**
 * Set of backtick-quoted identifiers (table + column + index names) defined
 * in OpenEMR's canonical schema file.
 *
 * Used by SqlReservedWordRule to suppress false positives: a token only fires
 * the rule if it's both a reserved word AND a known schema identifier.
 * Without this gate the rule would noisily flag identifiers like `over_field`
 * in arbitrary SQL whose unrelated columns happen to share a name with a
 * reserved word.
 *
 * The schema file is large (~15k lines), so parsing is cached to disk and
 * invalidated by mtime. Subsequent PHPStan invocations re-read the cache in
 * single-digit milliseconds.
 */
final readonly class SchemaColumnRegistry
{
    /**
     * @var array<string, true>
     */
    private array $identifiers;

    /**
     * @param string|null $schemaPath Absolute path to sql/database.sql, or
     *                                null to use OpenEMR's canonical schema.
     *                                Tests pass a fixture path here.
     * @param string|null $cachePath Optional path to a writable cache file.
     *                               If null, defaults to project tmp-phpstan
     *                               cache. Pass empty string to disable
     *                               caching (useful in tests).
     */
    public function __construct(?string $schemaPath = null, ?string $cachePath = null)
    {
        $schemaPath ??= __DIR__ . '/../../../../sql/database.sql';
        $cachePath ??= __DIR__ . '/../../../../tmp-phpstan/sql-schema-identifiers.php';
        if ($cachePath === '') {
            $cachePath = null;
        }
        $this->identifiers = $this->load($schemaPath, $cachePath);
    }

    public function isIdentifier(string $name): bool
    {
        return isset($this->identifiers[strtolower($name)]);
    }

    /**
     * @return array<string, true>
     */
    private function load(string $schemaPath, ?string $cachePath): array
    {
        if (!is_file($schemaPath)) {
            throw new RuntimeException("Schema file not found: {$schemaPath}");
        }

        $schemaMtime = filemtime($schemaPath);
        if ($schemaMtime === false) {
            throw new RuntimeException("Cannot stat schema file: {$schemaPath}");
        }

        if ($cachePath !== null && is_file($cachePath)) {
            $cacheMtime = filemtime($cachePath);
            if ($cacheMtime !== false && $cacheMtime >= $schemaMtime) {
                /** @var array<string, true>|mixed $cached */
                $cached = @include $cachePath;
                if (is_array($cached)) {
                    /** @var array<string, true> $cached */
                    return $cached;
                }
            }
        }

        $identifiers = $this->parse($schemaPath);

        if ($cachePath !== null) {
            $dir = dirname($cachePath);
            if (!is_dir($dir)) {
                @mkdir($dir, 0755, true);
            }
            $serialized = "<?php\n\nreturn " . var_export($identifiers, true) . ";\n";
            // PHPStan analyses in parallel workers. On a clean checkout
            // every worker misses the cache and races to write the same
            // file; a worker @include'ing a half-written file would hit
            // a parse error (which @ cannot suppress) and abort the run.
            // Write to a per-process temp file, then rename atomically
            // on the same filesystem.
            $tmp = $cachePath . '.' . getmypid() . '.tmp';
            if (@file_put_contents($tmp, $serialized) !== false) {
                if (!@rename($tmp, $cachePath)) {
                    @unlink($tmp);
                }
            }
        }

        return $identifiers;
    }

    /**
     * @return array<string, true>
     */
    private function parse(string $schemaPath): array
    {
        $contents = file_get_contents($schemaPath);
        if ($contents === false) {
            throw new RuntimeException("Cannot read schema file: {$schemaPath}");
        }

        // Backticks in OpenEMR's DDL only quote identifiers (table, column,
        // and index names). Single-quote-delimited values in COMMENT clauses
        // can't be confused with this pattern.
        preg_match_all('/`([a-zA-Z_][a-zA-Z0-9_]*)`/', $contents, $matches);

        $set = [];
        foreach ($matches[1] as $identifier) {
            $set[strtolower($identifier)] = true;
        }
        return $set;
    }
}
