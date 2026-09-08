<?php

/**
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc.
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Release;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Process\Process;

final class ExtractChangelogSectionCliTest extends TestCase
{
    private const BIN = __DIR__ . '/../../../../tools/release/bin/extract-changelog-section.php';

    private string $tmpDir = '';

    protected function setUp(): void
    {
        $this->tmpDir = sys_get_temp_dir() . '/openemr-extract-cli-' . bin2hex(random_bytes(8));
        if (!mkdir($this->tmpDir, 0700, true)) {
            throw new \RuntimeException('Failed to create tmp dir: ' . $this->tmpDir);
        }
    }

    protected function tearDown(): void
    {
        $this->removeRecursive($this->tmpDir);
    }

    public function testExtractsTargetVersionSectionOnlyBoundedByNextVersionHeading(): void
    {
        $changelog = <<<'MD'
# CHANGELOG.md

## [8.2.1](https://github.com/openemr/openemr/compare/v8_2_0...v8_2_1) - 2026-08-01

### Minimum supported versions

- **PHP** 8.2+

See the [tested CI matrix](https://github.com/openemr/openemr/tree/rel-820/ci) for all tested version combinations.

### Fixed

  - one bug ([#100](https://github.com/openemr/openemr/pull/100))

## [8.2.0](https://github.com/openemr/openemr/compare/v8_1_0...v8_2_0) - 2026-07-08

### Fixed

  - an earlier release's bug ([#1](https://github.com/openemr/openemr/pull/1))

MD;
        $changelogPath = $this->tmpDir . '/CHANGELOG.md';
        $outputPath = $this->tmpDir . '/extracted.md';
        file_put_contents($changelogPath, $changelog);

        $this->runBin([
            '--release-version=8.2.1',
            '--changelog=' . $changelogPath,
            '--output=' . $outputPath,
        ])->mustRun();

        $out = (string) file_get_contents($outputPath);
        // Only 8.2.1's section extracted -- 8.2.0's separate section not included
        self::assertStringStartsWith('## [8.2.1]', $out);
        self::assertStringContainsString('### Minimum supported versions', $out);
        self::assertStringContainsString('- **PHP** 8.2+', $out);
        self::assertStringContainsString('#100', $out);
        self::assertStringNotContainsString('## [8.2.0]', $out);
        self::assertStringNotContainsString('an earlier release', $out);
    }

    public function testExtractsFinalVersionSectionAllTheWayToEof(): void
    {
        $changelog = <<<'MD'
# CHANGELOG.md

## [8.2.1](https://github.com/openemr/openemr/compare/v8_2_0...v8_2_1) - 2026-08-01

### Fixed

  - new bug ([#200](https://github.com/openemr/openemr/pull/200))

## [8.2.0](https://github.com/openemr/openemr/compare/v8_1_0...v8_2_0) - 2026-07-08

### Fixed

  - old bug ([#1](https://github.com/openemr/openemr/pull/1))

MD;
        $changelogPath = $this->tmpDir . '/CHANGELOG.md';
        $outputPath = $this->tmpDir . '/extracted.md';
        file_put_contents($changelogPath, $changelog);

        $this->runBin([
            '--release-version=8.2.0',
            '--changelog=' . $changelogPath,
            '--output=' . $outputPath,
        ])->mustRun();

        $out = (string) file_get_contents($outputPath);
        self::assertStringStartsWith('## [8.2.0]', $out);
        self::assertStringContainsString('#1', $out);
        self::assertStringNotContainsString('#200', $out);
    }

    public function testMissingSectionExitsNonZero(): void
    {
        $changelog = "# CHANGELOG.md\n\n## [8.2.1](url) - 2026-08-01\n\n### Fixed\n\n  - a bug\n";
        $changelogPath = $this->tmpDir . '/CHANGELOG.md';
        $outputPath = $this->tmpDir . '/extracted.md';
        file_put_contents($changelogPath, $changelog);

        $process = $this->runBin([
            '--release-version=9.9.9',
            '--changelog=' . $changelogPath,
            '--output=' . $outputPath,
        ]);
        $process->run();

        self::assertFalse($process->isSuccessful());
        self::assertStringContainsString('No `## [9.9.9]` section found', $process->getErrorOutput());
        self::assertFileDoesNotExist($outputPath);
    }

    public function testMalformedVersionRejected(): void
    {
        $process = $this->runBin([
            '--release-version=v8.2.1',
            '--changelog=/dev/null',
            '--output=/dev/null',
        ]);
        $process->run();

        self::assertFalse($process->isSuccessful());
        self::assertStringContainsString('MAJOR.MINOR.PATCH', $process->getErrorOutput());
    }

    /**
     * @param list<string> $args
     */
    private function runBin(array $args): Process
    {
        return new Process(['php', self::BIN, ...$args]);
    }

    private function removeRecursive(string $path): void
    {
        if (!is_dir($path)) {
            return;
        }
        /** @var iterable<\SplFileInfo> $items */
        $items = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($path, \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::CHILD_FIRST,
        );
        foreach ($items as $item) {
            if ($item->isDir()) {
                rmdir($item->getPathname());
            } else {
                unlink($item->getPathname());
            }
        }
        rmdir($path);
    }
}
