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

use OpenEMR\Release\BranchVersionResolver;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Process\Process;

final class BranchVersionResolverTest extends TestCase
{
    private string $tmpDir = '';

    protected function setUp(): void
    {
        $this->tmpDir = sys_get_temp_dir() . '/openemr-branch-version-' . bin2hex(random_bytes(8));
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

    public function testBranchToVersionParsesRel810(): void
    {
        self::assertSame('8.1.0', BranchVersionResolver::branchToVersion('rel-810'));
    }

    public function testBranchToVersionParsesRel700(): void
    {
        self::assertSame('7.0.0', BranchVersionResolver::branchToVersion('rel-700'));
    }

    public function testBranchToVersionRejectsBadInput(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        BranchVersionResolver::branchToVersion('main');
    }

    public function testPreviousReleaseFallsBackWhenNoTagExists(): void
    {
        $resolver = new BranchVersionResolver($this->tmpDir);
        self::assertSame('8.0.0', $resolver->previousRelease('8.1.0'));
    }

    public function testPreviousReleaseReturnsLatestVersionTag(): void
    {
        $this->git(['tag', '-a', 'v8_0_0', '-m', 'OpenEMR 8.0.0 released 2026-01-01']);
        $this->git(['tag', '-a', 'v8_0_1', '-m', 'OpenEMR 8.0.1 released 2026-02-01']);
        $resolver = new BranchVersionResolver($this->tmpDir);
        self::assertSame('8.0.1', $resolver->previousRelease('8.1.0'));
    }

    public function testPreviousReleaseIgnoresNonReleaseVTags(): void
    {
        // A tag whose name starts with v but doesn't match the
        // v<MAJOR>_<MINOR>_<PATCH> pattern shouldn't be treated as a
        // release tag.
        $this->git(['tag', 'vendor-snapshot-2026-04']);
        $resolver = new BranchVersionResolver($this->tmpDir);
        self::assertSame('8.0.0', $resolver->previousRelease('8.1.0'));
    }

    public function testPreviousReleaseSkipsTagsAboveTarget(): void
    {
        // A future-version tag (e.g. someone pre-tagged 8.2.0 while
        // 8.1.0 is being cut) must not be returned as the previous
        // release of 8.1.0.
        $this->git(['tag', '-a', 'v8_0_5', '-m', 'OpenEMR 8.0.5 released 2026-03-01']);
        $this->git(['tag', '-a', 'v8_2_0', '-m', 'OpenEMR 8.2.0 released 2026-04-01']);
        $resolver = new BranchVersionResolver($this->tmpDir);
        self::assertSame('8.0.5', $resolver->previousRelease('8.1.0'));
    }

    public function testPreviousReleaseFallsBackWhenAllTagsAreAboveTarget(): void
    {
        // No qualifying tag below the target -> synthesise one. Same
        // behaviour as a fresh repo with no tags at all.
        $this->git(['tag', '-a', 'v8_2_0', '-m', 'OpenEMR 8.2.0 released 2026-04-01']);
        $resolver = new BranchVersionResolver($this->tmpDir);
        self::assertSame('8.0.0', $resolver->previousRelease('8.1.0'));
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
