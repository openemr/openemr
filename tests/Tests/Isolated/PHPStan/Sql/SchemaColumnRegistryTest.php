<?php

/**
 * Cache-path coverage for SchemaColumnRegistry. The RuleTestCase suite
 * disables caching (passes empty string), so this dedicated test exercises
 * the write-then-read cycle that powers cross-PHPStan-run reuse.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2026 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\PHPStan\Sql;

use OpenEMR\PHPStan\Rules\Sql\SchemaColumnRegistry;
use PHPUnit\Framework\TestCase;

final class SchemaColumnRegistryTest extends TestCase
{
    private string $tmpDir;
    private string $schemaPath;
    private string $cachePath;

    protected function setUp(): void
    {
        $tmpDir = sys_get_temp_dir() . '/oe-schema-registry-test-' . uniqid('', true);
        mkdir($tmpDir);
        $this->tmpDir = $tmpDir;
        $this->schemaPath = $tmpDir . '/schema.sql';
        $this->cachePath = $tmpDir . '/cache.json';
    }

    protected function tearDown(): void
    {
        @unlink($this->cachePath);
        @unlink($this->schemaPath);
        @rmdir($this->tmpDir);
    }

    public function testFirstConstructionWritesCacheFile(): void
    {
        file_put_contents($this->schemaPath, "CREATE TABLE `t` (`foo_col` int);");

        $registry = new SchemaColumnRegistry($this->schemaPath, $this->cachePath);

        self::assertTrue($registry->isIdentifier('foo_col'));
        self::assertFileExists($this->cachePath);
        $contents = file_get_contents($this->cachePath);
        self::assertIsString($contents);
        $cached = json_decode($contents, true);
        self::assertIsArray($cached);
        self::assertTrue($cached['foo_col'] ?? false);
    }

    public function testSecondConstructionReadsFromCacheWithoutReparsing(): void
    {
        file_put_contents($this->schemaPath, "CREATE TABLE `t` (`foo_col` int);");
        new SchemaColumnRegistry($this->schemaPath, $this->cachePath);

        // Overwrite the cache with a sentinel that could not have come from
        // parsing the schema. A second construction that re-parses would
        // discard this value; a cache hit returns it verbatim.
        file_put_contents(
            $this->cachePath,
            json_encode(['sentinel_from_cache' => true]),
        );
        // The cache mtime must be >= schema mtime for the hit path to fire;
        // touch() ensures that regardless of write-order timing.
        touch($this->cachePath, time() + 5);

        $registry = new SchemaColumnRegistry($this->schemaPath, $this->cachePath);

        self::assertTrue($registry->isIdentifier('sentinel_from_cache'));
        self::assertFalse($registry->isIdentifier('foo_col'));
    }

    public function testStaleCacheIsRebuiltWhenSchemaIsNewer(): void
    {
        file_put_contents(
            $this->cachePath,
            json_encode(['old_cached_col' => true]),
        );
        // Make the cache older than the schema so the staleness check fires.
        touch($this->cachePath, time() - 60);

        file_put_contents($this->schemaPath, "CREATE TABLE `t` (`current_col` int);");

        $registry = new SchemaColumnRegistry($this->schemaPath, $this->cachePath);

        self::assertTrue($registry->isIdentifier('current_col'));
        self::assertFalse($registry->isIdentifier('old_cached_col'));
    }
}
