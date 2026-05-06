<?php

/**
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Release;

use OpenEMR\Release\TagVerifier;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Process\Process;

final class TagVerifierTest extends TestCase
{
    private string $tmpDir = '';

    protected function setUp(): void
    {
        $this->tmpDir = sys_get_temp_dir() . '/openemr-tag-verifier-' . bin2hex(random_bytes(8));
        if (!mkdir($this->tmpDir, 0700, true)) {
            throw new \RuntimeException('Failed to create tmp dir: ' . $this->tmpDir);
        }
        $this->git(['init', '-q', '-b', 'main']);
        $this->git(['commit', '--allow-empty', '-q', '-m', 'initial commit']);
    }

    protected function tearDown(): void
    {
        $this->removeRecursive($this->tmpDir);
    }

    public function testValidAnnotatedTagPasses(): void
    {
        $sha = $this->git(['rev-parse', 'HEAD']);
        $message = sprintf(
            "OpenEMR 8.1.0 released 2026-04-29\n\nConductor PR: https://example.test/pr/1\nMerge commit: %s",
            $sha,
        );
        $this->git(['tag', '-a', 'v8_1_0', '-m', $message]);

        $result = (new TagVerifier($this->tmpDir))->verify('v8_1_0');

        self::assertTrue($result->isValid(), 'Expected valid tag, got errors: ' . implode('; ', $result->errors));
        self::assertTrue($result->isAnnotated);
        self::assertSame('8.1.0', $result->version);
        self::assertSame('2026-04-29', $result->date);
        self::assertSame($sha, $result->mergeSha);
    }

    public function testLightweightTagFails(): void
    {
        $this->git(['tag', 'v8_1_0']);

        $result = (new TagVerifier($this->tmpDir))->verify('v8_1_0');

        self::assertFalse($result->isValid());
        self::assertFalse($result->isAnnotated);
        self::assertCount(1, $result->errors);
        self::assertStringContainsString('not annotated', $result->errors[0]);
    }

    public function testMissingVersionFails(): void
    {
        $sha = $this->git(['rev-parse', 'HEAD']);
        $message = sprintf("Released 2026-04-29\n\nMerge commit: %s", $sha);
        $this->git(['tag', '-a', 'v8_1_0', '-m', $message]);

        $result = (new TagVerifier($this->tmpDir))->verify('v8_1_0');

        self::assertFalse($result->isValid());
        self::assertNull($result->version);
        self::assertContains('tag message does not contain a version (MAJOR.MINOR.PATCH)', $result->errors);
    }

    public function testMissingDateFails(): void
    {
        $sha = $this->git(['rev-parse', 'HEAD']);
        $message = sprintf("OpenEMR 8.1.0 released\n\nMerge commit: %s", $sha);
        $this->git(['tag', '-a', 'v8_1_0', '-m', $message]);

        $result = (new TagVerifier($this->tmpDir))->verify('v8_1_0');

        self::assertFalse($result->isValid());
        self::assertNull($result->date);
        self::assertContains('tag message does not contain an ISO date (YYYY-MM-DD)', $result->errors);
    }

    public function testMissingMergeShaFails(): void
    {
        $message = "OpenEMR 8.1.0 released 2026-04-29\n\nNo merge SHA here";
        $this->git(['tag', '-a', 'v8_1_0', '-m', $message]);

        $result = (new TagVerifier($this->tmpDir))->verify('v8_1_0');

        self::assertFalse($result->isValid());
        self::assertNull($result->mergeSha);
        self::assertContains('tag message does not contain a merge-commit SHA (40 hex chars)', $result->errors);
    }

    public function testCollectsAllErrorsInOnePass(): void
    {
        $message = 'minimal tag message with no required fields';
        $this->git(['tag', '-a', 'v8_1_0', '-m', $message]);

        $result = (new TagVerifier($this->tmpDir))->verify('v8_1_0');

        self::assertFalse($result->isValid());
        self::assertCount(3, $result->errors);
    }

    /**
     * @param list<string> $args
     */
    private function git(array $args): string
    {
        $process = new Process(['git', ...$args], $this->tmpDir, [
            'GIT_CONFIG_GLOBAL' => '/dev/null',
            'GIT_CONFIG_SYSTEM' => '/dev/null',
            'GIT_AUTHOR_NAME' => 'Test Author',
            'GIT_AUTHOR_EMAIL' => 'test@example.test',
            'GIT_COMMITTER_NAME' => 'Test Committer',
            'GIT_COMMITTER_EMAIL' => 'test@example.test',
        ]);
        $process->mustRun();
        return rtrim($process->getOutput(), "\n");
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
