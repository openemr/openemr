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
            $this->manifestClient(['8.0.0' => 'FINAL']),
        );
        self::assertSame('8.0.0', $resolver->previousRelease('8.2.0'));
    }

    public function testPreviousReleaseTrustsManifestOverAnnotatedTagFilter(): void
    {
        // Historic SourceForge-era v* tags are LIGHTWEIGHT, not annotated
        // (openemr/openemr's real v8_0_0 predates the annotated-tag policy
        // TagVerifier enforces on new cuts). The manifest is the source
        // of truth for what shipped; the resolver must trust it and NOT
        // discard 8.0.0 just because its tag is lightweight.
        //
        // Regression pin for the "8.2.0 dispatch got prev_release=8.1.0
        // even after the manifest fix" post-merge bug: v8_1_0 was
        // annotated + skipped, v8_0_0 was lightweight + shipped, so the
        // original manifest-filter walked the annotated-only set (just
        // v8_1_0), filtered v8_1_0 out as unshipped, fell through to
        // null, and previousRelease synthesised 8.1.0 as the prev-minor
        // fallback — the same skipped version we were trying to avoid.
        $this->git(['tag', 'v8_0_0']); // lightweight (no -a)
        $this->git(['tag', '-a', 'v8_1_0', '-m', 'cut and skipped']);
        $resolver = new BranchVersionResolver(
            $this->tmpDir,
            $this->manifestClient(['8.0.0' => 'FINAL']),
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
            $this->manifestClient(['8.0.0' => 'FINAL', '8.1.0' => 'FINAL']),
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
        $resolver = new BranchVersionResolver(
            $this->tmpDir,
            $this->manifestClient(['8.0.0' => 'FINAL', '8.1.0' => 'DRAFT']),
        );
        self::assertSame('8.0.0', $resolver->previousRelease('8.2.0'));
    }

    public function testPreviousReleaseFallsBackToTagsOnBadJson(): void
    {
        // Manifest fetch returns a 200 body that isn't valid JSON (some
        // upstream issue on raw.githubusercontent, cached error page,
        // etc.). Resolver's JSON-decode wrapper catches JsonException
        // and falls back to tag-only behaviour - accepting 8.1.0 as
        // prev, which is the pre-manifest behaviour. Preferable to a
        // hard failure that would block every release dispatch on any
        // parse hiccup.
        $this->git(['tag', '-a', 'v8_0_0', '-m', 'OpenEMR 8.0.0 released 2026-01-01']);
        $this->git(['tag', '-a', 'v8_1_0', '-m', 'OpenEMR 8.1.0 cut but skipped']);
        $client = new MockHttpClient([new MockResponse('not json at all', ['http_code' => 200])]);
        $resolver = new BranchVersionResolver($this->tmpDir, $client);
        self::assertSame('8.1.0', $resolver->previousRelease('8.2.0'));
    }

    public function testPreviousReleaseFallsBackToTagsOnHttpError(): void
    {
        // Manifest fetch gets a non-2xx status (rate limit, GitHub Pages
        // downtime, DNS blip). getContent() throws a ClientException /
        // ServerException (both extend HttpClientException); resolver's
        // try/catch on the HTTP call catches and falls back to tag-only.
        $this->git(['tag', '-a', 'v8_0_0', '-m', 'OpenEMR 8.0.0 released 2026-01-01']);
        $this->git(['tag', '-a', 'v8_1_0', '-m', 'OpenEMR 8.1.0 cut but skipped']);
        $client = new MockHttpClient([new MockResponse('server error', ['http_code' => 500])]);
        $resolver = new BranchVersionResolver($this->tmpDir, $client);
        self::assertSame('8.1.0', $resolver->previousRelease('8.2.0'));
    }

    /**
     * @param array<string, string> $versionStatuses map of version to status
     *   (e.g. `['8.0.0' => 'FINAL', '8.1.0' => 'DRAFT']`). Each entry gets
     *   a plausible-shaped stub of the fields data/releases.json entries
     *   carry - the fetchShippedVersions() reader only inspects `status`,
     *   so the rest of the payload just needs to be structurally valid.
     */
    private function manifestClient(array $versionStatuses): MockHttpClient
    {
        $entries = [];
        foreach ($versionStatuses as $version => $status) {
            $entries[$version] = ['status' => $status, 'released_at' => '2026-01-01'];
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
