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

/**
 * CLI-level coverage for tools/release/bin/verify-tag.php.
 *
 * TagVerifierTest already exhaustively covers the fixture-driven cases
 * (annotated vs lightweight, present vs missing components in the tag
 * message). This test only covers the CLI shell around it: the required
 * positional argument, an empty --repo-dir override, and a single
 * end-to-end happy-path invocation against a synthetic git repo carrying
 * an annotated tag whose message matches the openemr-devops#664 spec.
 */
final class VerifyTagCliTest extends TestCase
{
    private const BIN = __DIR__ . '/../../../../tools/release/bin/verify-tag.php';

    private string $tmpDir = '';

    protected function setUp(): void
    {
        $this->tmpDir = sys_get_temp_dir() . '/openemr-verify-tag-cli-' . bin2hex(random_bytes(8));
        if (!mkdir($this->tmpDir, 0700, true)) {
            throw new \RuntimeException('Failed to create tmp dir: ' . $this->tmpDir);
        }
    }

    protected function tearDown(): void
    {
        $this->removeRecursive($this->tmpDir);
    }

    public function testMissingTagPositionalRejected(): void
    {
        // Symfony Console rejects a missing REQUIRED positional argument
        // by raising RuntimeException("Not enough arguments...") which
        // surfaces via the default exception renderer to stderr.
        $process = new Process(['php', self::BIN]);
        $process->run();

        self::assertFalse($process->isSuccessful());
        self::assertStringContainsString('Not enough arguments', $process->getErrorOutput());
    }

    public function testEmptyRepoDirRejected(): void
    {
        // The bin file guards is_string+non-empty on --repo-dir. Because
        // Symfony's addOption default is `getcwd() ?: '.'` — non-empty —
        // this only fires when the operator explicitly overrides with
        // `--repo-dir=`.
        $process = new Process(['php', self::BIN, 'v8_1_0', '--repo-dir=']);
        $process->run();

        self::assertFalse($process->isSuccessful());
        self::assertSame(2, $process->getExitCode());
        self::assertStringContainsString('tag and --repo-dir are required', $process->getOutput());
    }

    public function testHappyPathAgainstSyntheticRepo(): void
    {
        // Build a minimal git repo with one commit and an annotated tag
        // carrying a message that satisfies the openemr-devops#664 spec
        // (version + ISO date + 40-hex merge-commit SHA). Confirm the
        // CLI exits 0 and reports success on stdout.
        $repoDir = $this->tmpDir . '/repo';
        mkdir($repoDir, 0700, true);

        $this->git($repoDir, ['init', '-q', '-b', 'main']);
        $this->git($repoDir, ['config', 'user.email', 'test@example.invalid']);
        $this->git($repoDir, ['config', 'user.name', 'Test']);
        // GPG signing tends to be enabled in dev-shell configs; suppress
        // it so `git tag -a` doesn't wedge on missing keys.
        $this->git($repoDir, ['config', 'commit.gpgsign', 'false']);
        $this->git($repoDir, ['config', 'tag.gpgsign', 'false']);
        file_put_contents($repoDir . '/README.md', "sample\n");
        $this->git($repoDir, ['add', 'README.md']);
        $this->git($repoDir, ['commit', '-q', '-m', 'initial']);

        // Grab the commit SHA for the tag message; the spec's regex only
        // requires 40-hex ANYWHERE in the message body.
        $revParse = new Process(['git', 'rev-parse', 'HEAD'], $repoDir);
        $revParse->mustRun();
        $sha = trim($revParse->getOutput());

        $message = "OpenEMR 8.1.0 released 2026-07-21\n\nMerge commit: {$sha}\n";
        $this->git($repoDir, ['tag', '-a', 'v8_1_0', '-m', $message]);

        $process = new Process(['php', self::BIN, 'v8_1_0', '--repo-dir=' . $repoDir]);
        $process->run();

        self::assertSame(
            0,
            $process->getExitCode(),
            'expected success; stdout: ' . $process->getOutput() . ' stderr: ' . $process->getErrorOutput(),
        );
        self::assertStringContainsString('OK: v8_1_0 is annotated', $process->getOutput());
    }

    /**
     * @param list<string> $args
     */
    private function git(string $cwd, array $args): void
    {
        (new Process(['git', ...$args], $cwd))->mustRun();
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
