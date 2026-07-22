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

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Process\Process;

/**
 * Arg-shell tests for tools/release/bin/patch-assemble.php.
 *
 * patch-assemble.php shells out to git + zip against a real openemr
 * checkout to build the cumulative overlay patch. The happy path
 * requires a full openemr working tree with the requested start-tag
 * present in history; both are out of scope for an isolated test.
 * These tests cover only the CLI's validation gates: required-flag
 * rejection and openemr-dir existence check.
 */
final class PatchAssembleCliTest extends TestCase
{
    private const BIN = __DIR__ . '/../../../../tools/release/bin/patch-assemble.php';

    private string $tmpDir = '';

    protected function setUp(): void
    {
        $this->tmpDir = sys_get_temp_dir() . '/openemr-patch-assemble-cli-' . bin2hex(random_bytes(8));
        if (!mkdir($this->tmpDir, 0700, true)) {
            throw new \RuntimeException('Failed to create tmp dir: ' . $this->tmpDir);
        }
    }

    protected function tearDown(): void
    {
        $this->removeRecursive($this->tmpDir);
    }

    /**
     * @param array<string, string> $args
     */
    #[DataProvider('missingRequiredFlagProvider')]
    public function testMissingRequiredFlagRejected(string $missing, array $args): void
    {
        // The CLI iterates the 4-required-flag list in order
        // (start-tag, branch, filename, openemr-dir) and errors on the
        // first missing one. Each provider case omits exactly one flag
        // to confirm the specific error message fires.
        $processArgs = ['php', self::BIN];
        foreach ($args as $flag => $value) {
            $processArgs[] = "--{$flag}={$value}";
        }
        $process = new Process($processArgs);
        $process->run();

        self::assertFalse($process->isSuccessful());
        self::assertStringContainsString("--{$missing} is required", $process->getOutput());
    }

    /**
     * @return array<string, array{string, array<string, string>}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function missingRequiredFlagProvider(): array
    {
        // openemr-dir is set to a real path in these cases so the
        // missing-flag check under test isn't shadowed by the later
        // openemr-dir existence check.
        $existing = sys_get_temp_dir();
        return [
            'missing start-tag' => [
                'start-tag',
                ['branch' => 'rel-810', 'filename' => 'patch.zip', 'openemr-dir' => $existing],
            ],
            'missing branch' => [
                'branch',
                ['start-tag' => 'v8_0_0', 'filename' => 'patch.zip', 'openemr-dir' => $existing],
            ],
            'missing filename' => [
                'filename',
                ['start-tag' => 'v8_0_0', 'branch' => 'rel-810', 'openemr-dir' => $existing],
            ],
            'missing openemr-dir' => [
                'openemr-dir',
                ['start-tag' => 'v8_0_0', 'branch' => 'rel-810', 'filename' => 'patch.zip'],
            ],
        ];
    }

    public function testNonexistentOpenemrDirRejected(): void
    {
        $missing = $this->tmpDir . '/does-not-exist';
        $process = new Process([
            'php',
            self::BIN,
            '--start-tag=v8_0_0',
            '--branch=rel-810',
            '--filename=patch.zip',
            '--openemr-dir=' . $missing,
            '--output-dir=' . $this->tmpDir . '/out',
        ]);
        $process->run();

        self::assertFalse($process->isSuccessful());
        self::assertStringContainsString('OpenEMR directory not found', $process->getOutput());
    }

    public function testCopyStylesFlagAcceptedWithNonexistentOpenemrDir(): void
    {
        // Prove --copy-styles parses at the option layer by combining it
        // with a nonexistent --openemr-dir. If --copy-styles were
        // unregistered, Symfony Console would abort at parse time with
        // "does not exist"; if it parses correctly, the CLI's
        // --openemr-dir existence check trips next with the deterministic
        // "OpenEMR directory not found" error. No git / zip subprocess
        // ever runs; failure is local + fast.
        $nonexistentDir = $this->tmpDir . '/does-not-exist-' . bin2hex(random_bytes(4));
        $process = new Process([
            'php',
            self::BIN,
            '--start-tag=v8_0_0',
            '--branch=rel-810',
            '--filename=patch.zip',
            '--openemr-dir=' . $nonexistentDir,
            '--output-dir=' . $this->tmpDir . '/out',
            '--copy-styles',
        ]);
        $process->run();

        self::assertFalse($process->isSuccessful());
        self::assertStringContainsString('OpenEMR directory not found', $process->getOutput());
        $combined = $process->getOutput() . $process->getErrorOutput();
        self::assertStringNotContainsString('does not exist', $combined);
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
