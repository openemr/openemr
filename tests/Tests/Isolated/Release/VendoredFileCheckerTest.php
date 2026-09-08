<?php

/**
 * @package   openemr
 * @link      https://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc.
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Release;

use OpenEMR\Release\VendoredDriftIssue;
use OpenEMR\Release\VendoredFileChecker;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class VendoredFileCheckerTest extends TestCase
{
    private const CANONICAL_JSON = <<<'JSON'
        {
            "$schema": "https://example.test/schema",
            "title": "test schema",
            "required": ["event", "data"],
            "properties": {
                "event": { "type": "string" },
                "data": { "type": "object" }
            }
        }
        JSON;

    private const CANONICAL_PHP_TEMPLATE = <<<'PHP'
        <?php

        declare(strict_types=1);

        namespace %s;

        final class TagVerifier
        {
            public function verify(string $tag): bool
            {
                return $tag !== '';
            }
        }
        PHP;

    private string $canonicalRoot = '';
    private string $consumerDir = '';

    protected function setUp(): void
    {
        $this->canonicalRoot = sys_get_temp_dir() . '/openemr-vendored-canon-' . bin2hex(random_bytes(8));
        $this->consumerDir = sys_get_temp_dir() . '/openemr-vendored-consumer-' . bin2hex(random_bytes(8));
        if (!mkdir($this->canonicalRoot, 0700, true) || !mkdir($this->consumerDir, 0700, true)) {
            throw new \RuntimeException('Failed to create tmp dirs');
        }
        $this->writeCanonicalFixtures($this->canonicalRoot);
    }

    protected function tearDown(): void
    {
        $this->removeRecursive($this->canonicalRoot);
        $this->removeRecursive($this->consumerDir);
    }

    public function testMatchingCopiesProduceNoIssues(): void
    {
        $this->writeCanonicalFixtures($this->consumerDir);

        $issues = (new VendoredFileChecker($this->canonicalRoot, $this->consumerDir))->check();

        self::assertSame([], $issues);
    }

    public function testJsonReorderedKeysAreEquivalent(): void
    {
        $this->writeCanonicalFixtures($this->consumerDir);
        $reordered = json_encode(
            [
                'properties' => [
                    'data' => ['type' => 'object'],
                    'event' => ['type' => 'string'],
                ],
                'required' => ['event', 'data'],
                'title' => 'test schema',
                '$schema' => 'https://example.test/schema',
            ],
            JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR,
        );
        file_put_contents($this->consumerDir . '/contracts/dispatch.schema.json', $reordered);

        $issues = (new VendoredFileChecker($this->canonicalRoot, $this->consumerDir))->check();

        self::assertSame([], $issues, 'JSON object key order must not affect equivalence');
    }

    public function testJsonReformattedWhitespaceIsEquivalent(): void
    {
        $this->writeCanonicalFixtures($this->consumerDir);
        $minified = json_encode(
            json_decode(self::CANONICAL_JSON, true, flags: JSON_THROW_ON_ERROR),
            JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR,
        );
        file_put_contents($this->consumerDir . '/contracts/dispatch.schema.json', $minified);

        $issues = (new VendoredFileChecker($this->canonicalRoot, $this->consumerDir))->check();

        self::assertSame([], $issues, 'JSON whitespace must not affect equivalence');
    }

    public function testJsonListReorderingIsFlagged(): void
    {
        $this->writeCanonicalFixtures($this->consumerDir);
        $reorderedList = json_encode(
            [
                '$schema' => 'https://example.test/schema',
                'title' => 'test schema',
                'required' => ['data', 'event'],
                'properties' => [
                    'event' => ['type' => 'string'],
                    'data' => ['type' => 'object'],
                ],
            ],
            JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR,
        );
        file_put_contents($this->consumerDir . '/contracts/dispatch.schema.json', $reorderedList);

        $issues = (new VendoredFileChecker($this->canonicalRoot, $this->consumerDir))->check();

        self::assertCount(1, $issues, 'JSON list element order is semantic and must trip drift');
        self::assertSame('contracts/dispatch.schema.json', $issues[0]->relativePath);
        self::assertSame('drift', $issues[0]->kind);
    }

    public function testPhpWithDifferentNamespaceIsEquivalent(): void
    {
        $this->writeCanonicalFixtures($this->consumerDir);
        file_put_contents(
            $this->consumerDir . '/src/TagVerifier.php',
            sprintf(self::CANONICAL_PHP_TEMPLATE, 'OpenEMR\\ReleaseDocs\\Release'),
        );

        $issues = (new VendoredFileChecker($this->canonicalRoot, $this->consumerDir))->check();

        self::assertSame([], $issues, 'Different vendored namespace must not trip drift');
    }

    public function testPhpDriftBeyondNamespaceIsFlagged(): void
    {
        $this->writeCanonicalFixtures($this->consumerDir);
        $modified = str_replace(
            "return \$tag !== '';",
            "return true;",
            sprintf(self::CANONICAL_PHP_TEMPLATE, 'OpenEMR\\Release'),
        );
        file_put_contents($this->consumerDir . '/src/TagVerifier.php', $modified);

        $issues = (new VendoredFileChecker($this->canonicalRoot, $this->consumerDir))->check();

        self::assertCount(1, $issues, 'Behavior change must trip drift even with matching namespace');
        self::assertSame('src/TagVerifier.php', $issues[0]->relativePath);
        self::assertSame('drift', $issues[0]->kind);
    }

    public function testInvalidJsonRaisesWithPathContext(): void
    {
        $this->writeCanonicalFixtures($this->consumerDir);
        file_put_contents($this->consumerDir . '/contracts/dispatch.schema.json', '{not json');

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('contracts/dispatch.schema.json');

        (new VendoredFileChecker($this->canonicalRoot, $this->consumerDir))->check();
    }

    public function testMissingConsumerCopyIsFlagged(): void
    {
        $copy = VendoredFileChecker::VENDORED_PATHS;
        array_pop($copy);
        $this->writeCanonicalFixtures($this->consumerDir, $copy);

        $issues = (new VendoredFileChecker($this->canonicalRoot, $this->consumerDir))->check();

        self::assertCount(1, $issues);
        self::assertSame('missing_consumer', $issues[0]->kind);
    }

    public function testMissingCanonicalIsFlagged(): void
    {
        unlink($this->canonicalRoot . '/contracts/dispatch.schema.json');
        $this->writeCanonicalFixtures($this->consumerDir);

        $issues = (new VendoredFileChecker($this->canonicalRoot, $this->consumerDir))->check();

        self::assertCount(1, $issues);
        self::assertSame('contracts/dispatch.schema.json', $issues[0]->relativePath);
        self::assertSame('missing_canonical', $issues[0]->kind);
    }

    public function testMultipleDriftKindsReportedTogether(): void
    {
        $this->writeFile(
            $this->consumerDir,
            'contracts/dispatch.schema.json',
            json_encode(['unrelated' => 'value'], JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR),
        );

        $issues = (new VendoredFileChecker($this->canonicalRoot, $this->consumerDir))->check();

        self::assertCount(count(VendoredFileChecker::VENDORED_PATHS), $issues);
        $kinds = array_map(static fn(VendoredDriftIssue $i): string => $i->kind, $issues);
        self::assertContains('drift', $kinds);
        self::assertContains('missing_consumer', $kinds);
    }

    public function testCanonicalListContainsContractAndTagFiles(): void
    {
        self::assertContains('contracts/dispatch.schema.json', VendoredFileChecker::VENDORED_PATHS);
        self::assertContains('src/TagVerifier.php', VendoredFileChecker::VENDORED_PATHS);
        self::assertContains('src/TagVerificationResult.php', VendoredFileChecker::VENDORED_PATHS);
    }

    public function testPathOverrideMapsCanonicalToConsumerLayout(): void
    {
        foreach (VendoredFileChecker::VENDORED_PATHS as $rel) {
            $consumerRel = $rel === 'src/TagVerifier.php' ? 'src/Release/TagVerifier.php' : $rel;
            $this->writeFile($this->consumerDir, $consumerRel, $this->canonicalContents($rel));
        }

        $issues = (new VendoredFileChecker(
            $this->canonicalRoot,
            $this->consumerDir,
            ['src/TagVerifier.php' => 'src/Release/TagVerifier.php'],
        ))->check();

        self::assertSame([], $issues);
    }

    public function testOverriddenPathDriftReportsConsumerRelativePath(): void
    {
        $this->writeCanonicalFixtures($this->consumerDir);
        $this->writeFile(
            $this->consumerDir,
            'src/Release/TagVerifier.php',
            sprintf(self::CANONICAL_PHP_TEMPLATE, 'OpenEMR\\Release') . "\n// extra\n",
        );

        $issues = (new VendoredFileChecker(
            $this->canonicalRoot,
            $this->consumerDir,
            ['src/TagVerifier.php' => 'src/Release/TagVerifier.php'],
        ))->check();

        self::assertCount(1, $issues);
        self::assertSame('src/Release/TagVerifier.php', $issues[0]->relativePath);
        self::assertSame('drift', $issues[0]->kind);
    }

    public function testUnknownOverrideKeyThrows(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        new VendoredFileChecker(
            $this->canonicalRoot,
            $this->consumerDir,
            ['not/a/canonical/path.php' => 'irrelevant'],
        );
    }

    /**
     * @return iterable<string, array{string}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function unsafeOverrideValueProvider(): iterable
    {
        yield 'absolute path' => ['/etc/passwd'];
        yield 'parent traversal' => ['../outside.php'];
        yield 'embedded parent traversal' => ['src/../../outside.php'];
        yield 'trailing parent traversal' => ['src/Release/..'];
        yield 'empty value' => [''];
    }

    #[DataProvider('unsafeOverrideValueProvider')]
    public function testUnsafeOverrideValueThrows(string $unsafeValue): void
    {
        $this->expectException(\InvalidArgumentException::class);

        new VendoredFileChecker(
            $this->canonicalRoot,
            $this->consumerDir,
            ['src/TagVerifier.php' => $unsafeValue],
        );
    }

    public function testNonStringOverrideValueThrows(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        new VendoredFileChecker(
            $this->canonicalRoot,
            $this->consumerDir,
            ['src/TagVerifier.php' => 123],
        );
    }

    public function testNonStringOverrideKeyThrows(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        new VendoredFileChecker(
            $this->canonicalRoot,
            $this->consumerDir,
            [42 => 'src/Release/TagVerifier.php'],
        );
    }

    /**
     * @param list<string>|null $only restrict which paths to write
     */
    private function writeCanonicalFixtures(string $root, ?array $only = null): void
    {
        $paths = $only ?? VendoredFileChecker::VENDORED_PATHS;
        foreach ($paths as $rel) {
            $this->writeFile($root, $rel, $this->canonicalContents($rel));
        }
    }

    private function canonicalContents(string $relativePath): string
    {
        $extension = pathinfo($relativePath, PATHINFO_EXTENSION);
        return match ($extension) {
            'json' => self::CANONICAL_JSON,
            'php' => sprintf(self::CANONICAL_PHP_TEMPLATE, 'OpenEMR\\Release'),
            default => "canonical:{$relativePath}\n",
        };
    }

    private function writeFile(string $root, string $rel, string $contents): void
    {
        $abs = $root . '/' . $rel;
        $dir = dirname($abs);
        if (!is_dir($dir) && !mkdir($dir, 0700, true)) {
            throw new \RuntimeException("Failed to mkdir: {$dir}");
        }
        file_put_contents($abs, $contents);
    }

    private function removeRecursive(string $path): void
    {
        if (!is_dir($path)) {
            if (is_file($path) || is_link($path)) {
                unlink($path);
            }
            return;
        }
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($path, \FilesystemIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::CHILD_FIRST,
        );
        /** @var \SplFileInfo $entry */
        foreach ($iterator as $entry) {
            $entryPath = $entry->getPathname();
            if ($entry->isDir() && !$entry->isLink()) {
                rmdir($entryPath);
            } else {
                unlink($entryPath);
            }
        }
        rmdir($path);
    }
}
