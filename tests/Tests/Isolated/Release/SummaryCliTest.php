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

final class SummaryCliTest extends TestCase
{
    private const BIN = __DIR__ . '/../../../../tools/release/bin/summary.php';

    private string $tmpDir = '';

    protected function setUp(): void
    {
        $this->tmpDir = sys_get_temp_dir() . '/openemr-summary-cli-' . bin2hex(random_bytes(8));
        if (!mkdir($this->tmpDir, 0700, true)) {
            throw new \RuntimeException('Failed to create tmp dir: ' . $this->tmpDir);
        }
    }

    protected function tearDown(): void
    {
        $this->removeRecursive($this->tmpDir);
    }

    public function testEmptyMilestoneAcceptedInDryRun(): void
    {
        // Regression for the Phase 5.5 verification-battery failure:
        // build-release.yml dispatches without a milestone input (it's
        // optional), and summary.php previously rejected the empty value
        // as `--milestone is required`. That aborted the workflow before
        // the artifact-upload step, so operators couldn't inspect the
        // dry-run's produced tarball/zip. See openemr/openemr#13115's
        // Phase 5.5 findings for the surrounding context.
        $outputPath = $this->tmpDir . '/summary.md';

        $this->runBin([
            '--type=full',
            '--milestone=',
            '--version-branch=rel-820',
            '--output-dir=' . $this->tmpDir,
            '--output=' . $outputPath,
            '--dry-run',
        ])->mustRun();

        $out = (string) file_get_contents($outputPath);
        self::assertStringContainsString('## Release Build Results', $out);
        self::assertStringContainsString('rel-820', $out);
        self::assertStringContainsString('(dry run)', $out);
    }

    public function testMilestoneOmittedAcceptedInDryRun(): void
    {
        // Same as the empty case but with --milestone flag entirely absent
        // rather than set to empty. Both paths should behave identically.
        $outputPath = $this->tmpDir . '/summary.md';

        $this->runBin([
            '--type=full',
            '--version-branch=rel-820',
            '--output-dir=' . $this->tmpDir,
            '--output=' . $outputPath,
            '--dry-run',
        ])->mustRun();

        self::assertFileExists($outputPath);
    }

    public function testMilestoneProvidedIncludedInSummary(): void
    {
        $outputPath = $this->tmpDir . '/summary.md';

        $this->runBin([
            '--type=full',
            '--milestone=8.2.0',
            '--version-branch=rel-820',
            '--release-tag=v8_2_0',
            '--output-dir=' . $this->tmpDir,
            '--output=' . $outputPath,
        ])->mustRun();

        $out = (string) file_get_contents($outputPath);
        self::assertStringContainsString('8.2.0', $out);
        self::assertStringContainsString('v8_2_0', $out);
    }

    public function testMissingTypeRejected(): void
    {
        $process = $this->runBin([
            '--milestone=8.2.0',
            '--output-dir=' . $this->tmpDir,
        ]);
        $process->run();

        self::assertFalse($process->isSuccessful());
        // summary.php writes error markers via $output->writeln('<error>…</error>')
        // which SingleCommandApplication routes to stdout with red styling,
        // NOT to stderr. Assert against getOutput() rather than
        // getErrorOutput() to match actual behavior.
        self::assertStringContainsString('--type is required', $process->getOutput());
    }

    public function testInvalidTypeRejected(): void
    {
        $process = $this->runBin([
            '--type=quarterly',
            '--milestone=8.2.0',
            '--output-dir=' . $this->tmpDir,
        ]);
        $process->run();

        self::assertFalse($process->isSuccessful());
        self::assertStringContainsString('must be "patch" or "full"', $process->getOutput());
    }

    public function testChecksumsAndChangelogRolledInWhenPresent(): void
    {
        // Populate the output dir with the sidecar files summary.php picks
        // up via glob (matches build-release.yml's on-disk layout after
        // package:assemble + checksum tasks run).
        file_put_contents($this->tmpDir . '/openemr-8.2.0.tar.gz.sha256', "abc123  openemr-8.2.0.tar.gz\n");
        file_put_contents($this->tmpDir . '/openemr-8.2.0.zip.sha256', "def456  openemr-8.2.0.zip\n");
        file_put_contents($this->tmpDir . '/changelog.md', "## [8.2.0] - 2026-07-08\n\n### Fixed\n\n- a bug\n");

        $outputPath = $this->tmpDir . '/summary.md';
        $this->runBin([
            '--type=full',
            '--milestone=8.2.0',
            '--output-dir=' . $this->tmpDir,
            '--output=' . $outputPath,
        ])->mustRun();

        $out = (string) file_get_contents($outputPath);
        self::assertStringContainsString('abc123', $out);
        self::assertStringContainsString('def456', $out);
        self::assertStringContainsString('a bug', $out);
    }

    public function testOutputAppendsRatherThanOverwrites(): void
    {
        // GITHUB_STEP_SUMMARY is append-mode by convention; the CLI mirrors
        // that behavior when writing to --output. Confirm two back-to-back
        // invocations against the same file leave both summaries intact
        // rather than the second clobbering the first.
        $outputPath = $this->tmpDir . '/summary.md';
        file_put_contents($outputPath, "pre-existing marker line\n");

        $this->runBin([
            '--type=full',
            '--milestone=8.2.0',
            '--output-dir=' . $this->tmpDir,
            '--output=' . $outputPath,
        ])->mustRun();

        $out = (string) file_get_contents($outputPath);
        self::assertStringStartsWith('pre-existing marker line', $out);
        self::assertStringContainsString('## Release Build Results', $out);
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
