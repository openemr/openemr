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

use PhpMyAdmin\SqlParser\Parser;
use PhpMyAdmin\SqlParser\Statements\CreateStatement;
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
        $cachePath ??= __DIR__ . '/../../../../tmp-phpstan/sql-schema-identifiers.json';
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
                $contents = @file_get_contents($cachePath);
                if ($contents !== false) {
                    $cached = json_decode($contents, true);
                    if (is_array($cached)) {
                        /** @var array<string, true> $cached */
                        return $cached;
                    }
                }
            }
        }

        $identifiers = $this->parse($schemaPath);

        if ($cachePath !== null) {
            $this->writeCache($cachePath, $identifiers);
        }

        return $identifiers;
    }

    /**
     * Writes the identifier set to disk using JSON rather than a
     * PHP-return file so reads can never accidentally execute code, and
     * uses an exclusive-create temp file with an unpredictable name to
     * defeat symlink races on shared filesystems. PHPStan's parallel
     * workers all race for the same cache on cold runs; rename() is
     * atomic on the same filesystem, so readers see a complete file or
     * the previous file -- never a half-written one.
     *
     * @param array<string, true> $identifiers
     */
    private function writeCache(string $cachePath, array $identifiers): void
    {
        $dir = dirname($cachePath);
        if (!is_dir($dir)) {
            @mkdir($dir, 0755, true);
        }

        $tmp = @tempnam($dir, 'sql-schema-identifiers.');
        if ($tmp === false) {
            return;
        }

        // tempnam() creates the file with default perms but we want an
        // exclusive-create truncation in case anything raced us between
        // tempnam() and now. 'xb' fails if the file disappeared and was
        // replaced (e.g. by a symlink to elsewhere).
        @unlink($tmp);
        $handle = @fopen($tmp, 'xb');
        if ($handle === false) {
            return;
        }

        $serialized = json_encode($identifiers);
        if ($serialized === false) {
            fclose($handle);
            @unlink($tmp);
            return;
        }

        if (@fwrite($handle, $serialized) === false) {
            fclose($handle);
            @unlink($tmp);
            return;
        }
        fclose($handle);
        @chmod($tmp, 0644);

        if (!@rename($tmp, $cachePath)) {
            @unlink($tmp);
        }
    }

    /**
     * Walks every CreateStatement in the schema file and collects table
     * names, column names, and index names. Using the parser rather than a
     * backtick-only regex catches identifiers regardless of whether the
     * schema author chose to backtick them -- the schema has a couple of
     * historical tables (ccda, recent_patients) whose columns sit bare,
     * which a regex over `\`name\`` would miss.
     *
     * @return array<string, true>
     */
    private function parse(string $schemaPath): array
    {
        $contents = file_get_contents($schemaPath);
        if ($contents === false) {
            throw new RuntimeException("Cannot read schema file: {$schemaPath}");
        }

        $set = [];
        $parser = new Parser($contents);
        foreach ($parser->statements as $statement) {
            if (!$statement instanceof CreateStatement) {
                continue;
            }
            if ($statement->name?->table !== null && $statement->name->table !== '') {
                $set[strtolower($statement->name->table)] = true;
            }
            // ->fields can be CreateDefinition[]|ArrayObj|null depending on
            // the CREATE flavor (TABLE definitions vs. CREATE PROCEDURE
            // parameter lists, etc.). Only TABLE definitions interest us.
            $fields = $statement->fields;
            if (!is_array($fields)) {
                continue;
            }
            foreach ($fields as $field) {
                if ($field->name !== null && $field->name !== '') {
                    $set[strtolower((string) $field->name)] = true;
                }
            }
        }

        return $set;
    }
}
