<?php

/**
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2026 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Release;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Process\Process;

/**
 * Arg-shell tests for tools/release/bin/package-assemble.php.
 *
 * package-assemble.php runs composer / npm / Ant against a checked-out
 * openemr tree to build the shipped tarball + zip; the happy path is
 * untestable in an isolated context. These tests exercise only the
 * CLI's validation gates: required-flag rejection, strict N.N.N version
 * shape, and openemr-dir existence check (which fires in the
 * PackageAssembler constructor/assemble() rather than the bin file
 * itself).
 */
final class PackageAssembleCliTest extends TestCase
{
    private const BIN = __DIR__ . '/../../../../tools/release/bin/package-assemble.php';

    private string $tmpDir = '';

    protected function setUp(): void
    {
        $this->tmpDir = sys_get_temp_dir() . '/openemr-package-assemble-cli-' . bin2hex(random_bytes(8));
        if (!mkdir($this->tmpDir, 0700, true)) {
            throw new \RuntimeException('Failed to create tmp dir: ' . $this->tmpDir);
        }
    }

    protected function tearDown(): void
    {
        $this->removeRecursive($this->tmpDir);
    }

    public function testMissingReleaseVersionRejected(): void
    {
        $process = new Process([
            'php',
            self::BIN,
            '--openemr-dir=' . $this->tmpDir,
        ]);
        $process->run();

        self::assertFalse($process->isSuccessful());
        self::assertStringContainsString('--release-version is required', $process->getOutput());
    }

    public function testMissingOpenemrDirRejected(): void
    {
        $process = new Process([
            'php',
            self::BIN,
            '--release-version=8.1.0',
        ]);
        $process->run();

        self::assertFalse($process->isSuccessful());
        self::assertStringContainsString('--openemr-dir is required', $process->getOutput());
    }

    #[DataProvider('malformedVersionProvider')]
    public function testMalformedVersionRejected(string $malformed): void
    {
        $process = new Process([
            'php',
            self::BIN,
            '--release-version=' . $malformed,
            '--openemr-dir=' . $this->tmpDir,
        ]);
        $process->run();

        self::assertFalse($process->isSuccessful());
        self::assertStringContainsString('--release-version must be N.N.N', $process->getOutput());
    }

    /**
     * @return array<string, array{string}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function malformedVersionProvider(): array
    {
        return [
            'v-prefix' => ['v8.2.0'],
            'two segments' => ['8.2'],
            'pre-release suffix' => ['8.2.0-beta'],
        ];
    }

    public function testNonexistentOpenemrDirRejected(): void
    {
        // openemr-dir must exist. PackageAssembler::assemble() prints
        // "OpenEMR directory not found: ..." and returns 1.
        $missing = $this->tmpDir . '/does-not-exist';
        $process = new Process([
            'php',
            self::BIN,
            '--release-version=8.1.0',
            '--openemr-dir=' . $missing,
        ]);
        $process->run();

        self::assertFalse($process->isSuccessful());
        self::assertStringContainsString('OpenEMR directory not found', $process->getOutput());
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
