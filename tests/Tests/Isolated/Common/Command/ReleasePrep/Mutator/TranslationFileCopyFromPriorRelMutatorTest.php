<?php

/**
 * Tests for TranslationFileCopyFromPriorRelMutator: copies the
 * currentLanguage_utf8.sql blob from the prior rel branch via git
 * fetch + git show. The Process factory is injected so these tests
 * don't touch the network or require a real git repo.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2026 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Common\Command\ReleasePrep\Mutator;

use OpenEMR\Common\Command\ReleasePrep\Mutator\TranslationFileCopyFromPriorRelMutator;
use OpenEMR\Common\Command\ReleasePrep\MutatorContext;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Process\Process;

#[Group('isolated')]
#[Group('release-prep')]
final class TranslationFileCopyFromPriorRelMutatorTest extends TestCase
{
    private const RELATIVE_PATH = 'contrib/util/language_translations/currentLanguage_utf8.sql';

    private string $tmpDir = '';

    protected function setUp(): void
    {
        $this->tmpDir = sys_get_temp_dir() . '/openemr-tfc-' . bin2hex(random_bytes(8));
        if (!mkdir($this->tmpDir . '/contrib/util/language_translations', 0700, true)) {
            throw new \RuntimeException('Failed to create tmp dir');
        }
    }

    protected function tearDown(): void
    {
        $this->removeRecursive($this->tmpDir);
    }

    public function testReplacesLocalFileWithPriorRelContent(): void
    {
        $this->writeLocal('-- local: master tip content\n');

        $priorBlob = "-- prior rel-810 tip blob\nINSERT INTO lang ...\n";
        $factory = $this->stubProcessFactory(true, $priorBlob, '');

        $result = (new TranslationFileCopyFromPriorRelMutator($factory))->apply($this->context('rel-810'));
        self::assertTrue($result->changed());
        self::assertSame($priorBlob, $this->readLocal());
    }

    public function testIdempotentWhenLocalAlreadyMatchesPriorBlob(): void
    {
        $priorBlob = "-- already matches\n";
        $this->writeLocal($priorBlob);

        $factory = $this->stubProcessFactory(true, $priorBlob, '');
        $result = (new TranslationFileCopyFromPriorRelMutator($factory))->apply($this->context('rel-810'));
        self::assertFalse($result->changed());
    }

    public function testThrowsWhenPrevRelBranchMissing(): void
    {
        $this->writeLocal('foo');
        $context = MutatorContext::fromVersionString($this->tmpDir, '8.2.0', null, 'rel-820');

        $factory = $this->stubProcessFactory(true, '', '');
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessageMatches('/--prev-rel-branch/');
        (new TranslationFileCopyFromPriorRelMutator($factory))->apply($context);
    }

    public function testThrowsOnFetchFailure(): void
    {
        $this->writeLocal('foo');

        // Simulate fetch failure (factory returns Process objects that
        // will report unsuccessful exit). We use a real Process that
        // runs `false` to get a non-zero exit.
        $factory = static fn (array $cmd, string $cwd): Process => new Process(['false'], $cwd);
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessageMatches('/git fetch.*failed/');
        (new TranslationFileCopyFromPriorRelMutator($factory))->apply($this->context('rel-810'));
    }

    public function testThrowsOnShowFailure(): void
    {
        $this->writeLocal('foo');

        // First call (fetch) succeeds; second call (show) fails.
        $callCount = 0;
        $factory = static function (array $cmd, string $cwd) use (&$callCount): Process {
            $callCount++;
            if ($callCount === 1) {
                return new Process(['true'], $cwd);
            }
            return new Process(['false'], $cwd);
        };
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessageMatches('/git show.*failed/');
        (new TranslationFileCopyFromPriorRelMutator($factory))->apply($this->context('rel-810'));
    }

    private function context(string $prevRelBranch): MutatorContext
    {
        return MutatorContext::fromVersionString(
            $this->tmpDir,
            '8.2.0',
            null,
            'rel-820',
            $prevRelBranch,
        );
    }

    /**
     * Build a process factory that returns Processes which, when
     * ->run() is invoked, behave according to the stubbed values. We
     * achieve this by chaining real `printf`/`true`/`false` invocations.
     *
     * @return \Closure(list<string>, string): Process
     */
    private function stubProcessFactory(bool $success, string $stdout, string $stderr): \Closure
    {
        return static function (array $cmd, string $cwd) use ($success, $stdout, $stderr): Process {
            // First call: fetch (no stdout consumed). Second: show (stdout
            // is the blob). Easiest cross-platform stub: shell with printf
            // for show, true/false for fetch. But we'd need state to
            // distinguish. Instead, return a Process whose command writes
            // the desired stdout/stderr and exits with the right code.
            if ($cmd[1] === 'show') {
                // Use bash to emit exact bytes; encode via base64 to
                // handle arbitrary content (newlines, quotes, etc.).
                $b64 = base64_encode($stdout);
                $shell = 'printf %s "' . $b64 . '" | base64 -d; '
                    . 'printf %s "' . base64_encode($stderr) . '" | base64 -d >&2; '
                    . 'exit ' . ($success ? 0 : 1);
                return Process::fromShellCommandline($shell, $cwd);
            }
            // fetch: just succeed/fail without output.
            return Process::fromShellCommandline($success ? 'true' : 'false', $cwd);
        };
    }

    private function writeLocal(string $content): void
    {
        $path = $this->tmpDir . '/' . self::RELATIVE_PATH;
        if (file_put_contents($path, $content) === false) {
            throw new \RuntimeException('Cannot write local file');
        }
    }

    private function readLocal(): string
    {
        $path = $this->tmpDir . '/' . self::RELATIVE_PATH;
        $contents = file_get_contents($path);
        if ($contents === false) {
            throw new \RuntimeException('Cannot read local file');
        }
        return $contents;
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
            $p = $entry->getPathname();
            if ($entry->isDir() && !$entry->isLink()) {
                rmdir($p);
            } else {
                unlink($p);
            }
        }
        rmdir($path);
    }
}
