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
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;
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

    public function testBranchToVersionCutsDotZeroWhenMinorLineHasNoTag(): void
    {
        $resolver = new BranchVersionResolver($this->tmpDir);
        self::assertSame('8.1.0', $resolver->branchToVersion('rel-810'));
    }

    public function testBranchToVersionCutsDotZeroOnFreshRepoForRel700(): void
    {
        $resolver = new BranchVersionResolver($this->tmpDir);
        self::assertSame('7.0.0', $resolver->branchToVersion('rel-700'));
    }

    public function testBranchToVersionCutsNextPatchAfterExistingRelease(): void
    {
        $this->git(['tag', '-a', 'v8_1_0', '-m', 'OpenEMR 8.1.0 released 2026-05-01']);
        $resolver = new BranchVersionResolver($this->tmpDir);
        self::assertSame('8.1.1', $resolver->branchToVersion('rel-810'));
    }

    public function testBranchToVersionPicksHighestPatchOnMinorLine(): void
    {
        $this->git(['tag', '-a', 'v8_1_0', '-m', 'OpenEMR 8.1.0 released 2026-05-01']);
        $this->git(['tag', '-a', 'v8_1_1', '-m', 'OpenEMR 8.1.1 released 2026-05-15']);
        $this->git(['tag', '-a', 'v8_1_2', '-m', 'OpenEMR 8.1.2 released 2026-06-01']);
        $resolver = new BranchVersionResolver($this->tmpDir);
        self::assertSame('8.1.3', $resolver->branchToVersion('rel-810'));
    }

    public function testBranchToVersionIgnoresTagsOnOtherMinorLines(): void
    {
        // Tags on 8.0.x and 8.2.x must not influence the patch chosen
        // for the 8.1 line.
        $this->git(['tag', '-a', 'v8_0_5', '-m', 'OpenEMR 8.0.5 released 2026-03-01']);
        $this->git(['tag', '-a', 'v8_2_0', '-m', 'OpenEMR 8.2.0 released 2026-07-01']);
        $resolver = new BranchVersionResolver($this->tmpDir);
        self::assertSame('8.1.0', $resolver->branchToVersion('rel-810'));
    }

    public function testBranchToVersionIgnoresNonReleaseVTags(): void
    {
        $this->git(['tag', 'v8_1_x-nightly']);
        $resolver = new BranchVersionResolver($this->tmpDir);
        self::assertSame('8.1.0', $resolver->branchToVersion('rel-810'));
    }

    public function testBranchToVersionIgnoresLightweightReleaseShapedTags(): void
    {
        // A lightweight tag whose name matches the release pattern must
        // not bump the patch: the spec requires annotated tags, so only
        // an annotated v8_1_0 below counts toward the next version.
        $this->git(['tag', '-a', 'v8_1_0', '-m', 'OpenEMR 8.1.0 released 2026-05-01']);
        $this->git(['tag', 'v8_1_5']);
        $resolver = new BranchVersionResolver($this->tmpDir);
        self::assertSame('8.1.1', $resolver->branchToVersion('rel-810'));
    }

    public function testBranchToVersionRejectsBadInput(): void
    {
        $resolver = new BranchVersionResolver($this->tmpDir);
        $this->expectException(\InvalidArgumentException::class);
        $resolver->branchToVersion('main');
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

    public function testPreviousReleaseSkipsTagsMissingFromManifest(): void
    {
        // 8.1.0 was cut as an annotated v8_1_0 tag but never released -
        // it's absent from data/releases.json. When computing prev_release
        // for 8.2.0, the resolver must skip 8.1.0 and fall through to
        // 8.0.0 (the last actually-shipped release).
        $this->git(['tag', '-a', 'v8_0_0', '-m', 'OpenEMR 8.0.0 released 2026-01-01']);
        $this->git(['tag', '-a', 'v8_1_0', '-m', 'OpenEMR 8.1.0 cut but skipped']);
        $resolver = new BranchVersionResolver(
            $this->tmpDir,
            $this->manifestClient(['8.0.0']),
        );
        self::assertSame('8.0.0', $resolver->previousRelease('8.2.0'));
    }

    public function testPreviousReleaseAcceptsTagsPresentInManifest(): void
    {
        // Same tag set as above, but this time 8.1.0 shipped normally.
        // The manifest contains both entries; both are eligible; 8.1.0
        // wins as the newest below target 8.2.0.
        $this->git(['tag', '-a', 'v8_0_0', '-m', 'OpenEMR 8.0.0 released 2026-01-01']);
        $this->git(['tag', '-a', 'v8_1_0', '-m', 'OpenEMR 8.1.0 released 2026-06-01']);
        $resolver = new BranchVersionResolver(
            $this->tmpDir,
            $this->manifestClient(['8.0.0', '8.1.0']),
        );
        self::assertSame('8.1.0', $resolver->previousRelease('8.2.0'));
    }

    public function testPreviousReleaseTreatsDraftEntriesAsUnshipped(): void
    {
        // A DRAFT entry in the manifest is not FINAL, so its version is
        // not considered shipped. In practice this covers the case where
        // the release-docs bot pre-populates a DRAFT entry for the next
        // release before it flips FINAL - that draft mustn't be used as
        // a prev_release for anything.
        $this->git(['tag', '-a', 'v8_0_0', '-m', 'OpenEMR 8.0.0 released 2026-01-01']);
        $this->git(['tag', '-a', 'v8_1_0', '-m', 'OpenEMR 8.1.0 draft']);
        $manifest = [
            '8.0.0' => ['status' => 'FINAL',  'released_at' => '2026-01-01'],
            '8.1.0' => ['status' => 'DRAFT',  'branch' => 'rel-810', 'sha' => str_repeat('0', 40), 'released_at' => null],
        ];
        $client = new MockHttpClient([new MockResponse((string) json_encode($manifest))]);
        $resolver = new BranchVersionResolver($this->tmpDir, $client);
        self::assertSame('8.0.0', $resolver->previousRelease('8.2.0'));
    }

    public function testPreviousReleaseFallsBackToTagsWhenManifestFetchFails(): void
    {
        // Network hiccup / rate-limit / bad JSON should never crash the
        // conductor. When the manifest fetch fails the resolver falls
        // back to tag-only behaviour - accepting 8.1.0 as prev, which is
        // the pre-manifest behaviour. Preferable to a hard failure that
        // would block every release dispatch on any GitHub raw-content
        // hiccup.
        $this->git(['tag', '-a', 'v8_0_0', '-m', 'OpenEMR 8.0.0 released 2026-01-01']);
        $this->git(['tag', '-a', 'v8_1_0', '-m', 'OpenEMR 8.1.0 cut but skipped']);
        $client = new MockHttpClient([new MockResponse('not json at all', ['http_code' => 200])]);
        $resolver = new BranchVersionResolver($this->tmpDir, $client);
        self::assertSame('8.1.0', $resolver->previousRelease('8.2.0'));
    }

    /**
     * @param list<string> $shippedVersions
     */
    private function manifestClient(array $shippedVersions): MockHttpClient
    {
        $entries = [];
        foreach ($shippedVersions as $version) {
            $entries[$version] = ['status' => 'FINAL', 'released_at' => '2026-01-01'];
        }
        return new MockHttpClient([new MockResponse((string) json_encode($entries))]);
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
