<?php

/**
 * Isolated tests for ChangelogMutator: git-tag resolution,
 * rangeHead selection, CHANGELOG.md prepend + replace idempotence, and
 * the MutatorResult shape.
 *
 * Each test spins up a fresh temp git repo with pinned `v<M>_<m>_<p>`
 * tags so prev-tag resolution is deterministic without depending on the
 * calling checkout's history.
 *
 * The generator dependency is injected via ChangelogMutator's
 * constructor so no `gh` shell-out actually fires.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Release;

use OpenEMR\Common\Command\ReleasePrep\MutatorContext;
use OpenEMR\Release\ChangelogGenerator;
use OpenEMR\Release\GitHubApi;
use OpenEMR\Release\Mutator\ChangelogMutator;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Process\Process;

final class ChangelogMutatorTest extends TestCase
{
    private string $tmpDir = '';

    protected function setUp(): void
    {
        $this->tmpDir = sys_get_temp_dir() . '/openemr-changelog-mut-' . bin2hex(random_bytes(8));
        if (!mkdir($this->tmpDir, 0700, true)) {
            throw new \RuntimeException('Failed to create tmp dir: ' . $this->tmpDir);
        }
        $this->git(['init', '-q', '-b', 'main']);
        $this->git(['commit', '--allow-empty', '-q', '-m', 'initial']);
    }

    protected function tearDown(): void
    {
        $this->removeRecursive($this->tmpDir);
    }

    public function testPrependsNewSectionAfterHeaderWhenFileHasPriorEntries(): void
    {
        $this->git(['tag', 'v8_1_0']);
        $this->git(['tag', 'v8_2_0']);
        file_put_contents($this->tmpDir . '/CHANGELOG.md', <<<'MD'
# CHANGELOG.md

## [8.2.0](https://github.com/openemr/openemr/compare/v8_1_0...v8_2_0) - 2026-07-08

### Fixed

  - some prior 8.2.0 bug ([#100](https://github.com/openemr/openemr/pull/100))


MD);

        $result = $this->apply('8.2.1', relBranch: 'rel-820');
        self::assertTrue($result->changed());
        $updated = (string) file_get_contents($this->tmpDir . '/CHANGELOG.md');
        self::assertStringContainsString('## [8.2.1]', $updated);
        self::assertStringContainsString('## [8.2.0]', $updated);
        self::assertLessThan(
            strpos($updated, '## [8.2.0]'),
            strpos($updated, '## [8.2.1]'),
            'new 8.2.1 section appears before existing 8.2.0 section',
        );
    }

    public function testReplacesExistingSectionInPlace(): void
    {
        $this->git(['tag', 'v8_1_0']);
        $this->git(['tag', 'v8_2_0']);
        file_put_contents($this->tmpDir . '/CHANGELOG.md', <<<'MD'
# CHANGELOG.md

## [8.2.1](https://github.com/openemr/openemr/compare/v8_2_0...v8_2_1) - 2026-07-10

### Fixed

  - stale content from a prior release-prep run ([#999](https://github.com/openemr/openemr/pull/999))

## [8.2.0](https://github.com/openemr/openemr/compare/v8_1_0...v8_2_0) - 2026-07-08

### Fixed

  - some prior 8.2.0 bug ([#100](https://github.com/openemr/openemr/pull/100))


MD);

        $this->apply('8.2.1', relBranch: 'rel-820');
        $updated = (string) file_get_contents($this->tmpDir . '/CHANGELOG.md');

        self::assertStringNotContainsString('#999', $updated);
        self::assertStringContainsString('#100', $updated, '8.2.0 section preserved');
        self::assertSame(1, substr_count($updated, '## [8.2.1]'), 'no duplicate 8.2.1 section');
    }

    public function testRerunProducesNoOpWhenContentUnchanged(): void
    {
        $this->git(['tag', 'v8_1_0']);
        $this->git(['tag', 'v8_2_0']);
        file_put_contents($this->tmpDir . '/CHANGELOG.md', "# CHANGELOG.md\n\n");

        $first = $this->apply('8.2.1', relBranch: 'rel-820');
        self::assertTrue($first->changed(), 'first run makes a change');

        $second = $this->apply('8.2.1', relBranch: 'rel-820');
        self::assertFalse($second->changed(), 'idempotent second run reports no-op');
    }

    /**
     * The post-GHSA-publish amendment workflow re-runs the mutator
     * against a CHANGELOG that already contains the release-time
     * section (no Security block), this time with GHSAs published --
     * so the mutator's generator dependency now returns a body that
     * includes `### Security Fixes`. Assert:
     *  1. the second run replaces the release-time section in place
     *     (single `## [X.Y.Z]` header, now including Security block);
     *  2. a third identical run is byte-identical to the second (no
     *     drift, no duplication of the Security block);
     *  3. running a fourth time with the release-time generator again
     *     (e.g. someone accidentally dispatches with a GHSA
     *     unpublished) cleanly *removes* the Security block --
     *     wholesale replace, not additive.
     * This covers the amendment workflow's idempotence in both
     * directions (empty->populated and populated->empty).
     */
    public function testAmendmentReplacesSectionWholesaleAcrossReleaseTimeAndPostGhsa(): void
    {
        $this->git(['tag', 'v8_1_0']);
        $this->git(['tag', 'v8_2_0']);
        file_put_contents($this->tmpDir . '/CHANGELOG.md', "# CHANGELOG.md\n\n");

        $releaseTimeBody = "## [8.2.1](https://github.com/openemr/openemr/compare/v8_2_0...v8_2_1) - 2026-07-12\n\n"
            . "### Fixed\n\n  - some bug ([#1](https://github.com/openemr/openemr/pull/1))\n\n";
        $postGhsaBody = "## [8.2.1](https://github.com/openemr/openemr/compare/v8_2_0...v8_2_1) - 2026-07-12\n\n"
            . "### Security Fixes\n\n  - [High] some sec fix ([GHSA-xxxx-xxxx-xxxx](https://x))\n\n"
            . "### Fixed\n\n  - some bug ([#1](https://github.com/openemr/openemr/pull/1))\n\n";

        $gen = new SwitchableChangelogGenerator();
        $gen->body = $releaseTimeBody;

        // Run 1: release-time (no Security block).
        $this->apply('8.2.1', relBranch: 'rel-820', generator: $gen);
        $afterReleaseTime = (string) file_get_contents($this->tmpDir . '/CHANGELOG.md');
        self::assertStringNotContainsString('### Security Fixes', $afterReleaseTime);
        self::assertSame(1, substr_count($afterReleaseTime, '## [8.2.1]'));

        // Run 2: post-ghsa amendment adds the Security block.
        $gen->body = $postGhsaBody;
        $this->apply('8.2.1', relBranch: 'rel-820', generator: $gen);
        $afterPostGhsa = (string) file_get_contents($this->tmpDir . '/CHANGELOG.md');
        self::assertStringContainsString('### Security Fixes', $afterPostGhsa);
        self::assertStringContainsString('GHSA-xxxx-xxxx-xxxx', $afterPostGhsa);
        self::assertSame(
            1,
            substr_count($afterPostGhsa, '## [8.2.1]'),
            'wholesale replace: exactly one 8.2.1 section header after amendment',
        );
        self::assertSame(
            1,
            substr_count($afterPostGhsa, '### Security Fixes'),
            'wholesale replace: exactly one Security Fixes header after amendment',
        );
        self::assertSame(
            1,
            substr_count($afterPostGhsa, '### Fixed'),
            'wholesale replace: exactly one Fixed header after amendment',
        );

        // Run 3: identical amendment inputs -> byte-identical output.
        $this->apply('8.2.1', relBranch: 'rel-820', generator: $gen);
        self::assertSame(
            $afterPostGhsa,
            (string) file_get_contents($this->tmpDir . '/CHANGELOG.md'),
            'rerunning amendment with same inputs is byte-identical (no drift)',
        );

        // Run 4: revert to release-time body -> Security block gone.
        $gen->body = $releaseTimeBody;
        $this->apply('8.2.1', relBranch: 'rel-820', generator: $gen);
        $afterRevert = (string) file_get_contents($this->tmpDir . '/CHANGELOG.md');
        self::assertStringNotContainsString(
            '### Security Fixes',
            $afterRevert,
            'wholesale replace cleanly removes Security block when generator no longer emits one',
        );
        self::assertSame(
            $afterReleaseTime,
            $afterRevert,
            'reverting to release-time inputs restores byte-identical original release-time output',
        );
    }

    public function testRangeHeadUsesTargetTagWhenTagExists(): void
    {
        $this->git(['tag', 'v8_1_0']);
        $this->git(['tag', 'v8_2_0']);
        // Simulate finalize-partner-PR flow: v8_2_1 tag already exists.
        $this->git(['tag', 'v8_2_1']);
        file_put_contents($this->tmpDir . '/CHANGELOG.md', "# CHANGELOG.md\n\n");

        $capture = $this->captureGeneratorArgs();
        $this->apply('8.2.1', relBranch: 'rel-820', generator: $capture);

        self::assertSame('v8_2_0', $capture->base);
        self::assertSame('v8_2_1', $capture->head, 'existing tag preferred over rel branch');
        self::assertSame('v8_2_1', $capture->compareLinkOverride);
    }

    public function testRangeHeadFallsBackToRelBranchWhenTagAbsent(): void
    {
        $this->git(['tag', 'v8_1_0']);
        $this->git(['tag', 'v8_2_0']);
        file_put_contents($this->tmpDir . '/CHANGELOG.md', "# CHANGELOG.md\n\n");

        $capture = $this->captureGeneratorArgs();
        $this->apply('8.2.1', relBranch: 'rel-820', generator: $capture);

        self::assertSame('v8_2_0', $capture->base);
        self::assertSame('rel-820', $capture->head, 'rel branch used when target tag not yet created');
        self::assertSame(
            'v8_2_1',
            $capture->compareLinkOverride,
            'compareLinkOverride still points at aspirational vNEW',
        );
    }

    public function testPrevTagResolutionPicksHighestVersionLessThanTarget(): void
    {
        $this->git(['tag', 'v7_0_0']);
        $this->git(['tag', 'v8_0_0']);
        $this->git(['tag', 'v8_1_0']);
        $this->git(['tag', 'v8_1_1']);
        $this->git(['tag', 'v8_2_0']);
        // Note: v8_2_1 does not yet exist.
        file_put_contents($this->tmpDir . '/CHANGELOG.md', "# CHANGELOG.md\n\n");

        $capture = $this->captureGeneratorArgs();
        $this->apply('8.2.1', relBranch: 'rel-820', generator: $capture);

        self::assertSame('v8_2_0', $capture->base, 'highest tag strictly less than 8.2.1');
    }

    public function testPrevTagResolutionThrowsWhenNoPriorTagExists(): void
    {
        file_put_contents($this->tmpDir . '/CHANGELOG.md', "# CHANGELOG.md\n\n");

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessageMatches('/no `v.*` tag exists earlier than/');
        $this->apply('8.2.1', relBranch: 'rel-820');
    }

    public function testMutatorResultReportsChangelogPathAndSummary(): void
    {
        $this->git(['tag', 'v8_2_0']);
        file_put_contents($this->tmpDir . '/CHANGELOG.md', "# CHANGELOG.md\n\n");

        $result = $this->apply('8.2.1', relBranch: 'rel-820');
        self::assertSame(['CHANGELOG.md'], $result->changedFiles);
        self::assertCount(1, $result->messages);
        self::assertStringContainsString('8.2.1', $result->messages[0]);
        self::assertStringContainsString('v8_2_0...v8_2_1', $result->messages[0]);
    }

    private function apply(string $version, ?string $relBranch, ?ChangelogGenerator $generator = null): \OpenEMR\Common\Command\ReleasePrep\MutatorResult
    {
        $context = MutatorContext::fromVersionString($this->tmpDir, $version, $relBranch);
        $mutator = new ChangelogMutator($generator ?? new StubChangelogGenerator());
        return $mutator->apply($context);
    }

    private function captureGeneratorArgs(): CapturingChangelogGenerator
    {
        return new CapturingChangelogGenerator();
    }

    /**
     * @param list<string> $args
     */
    private function git(array $args): void
    {
        $process = new Process(
            ['git', ...$args],
            $this->tmpDir,
            [
                'GIT_AUTHOR_NAME' => 'Test',
                'GIT_AUTHOR_EMAIL' => 'test@example.com',
                'GIT_COMMITTER_NAME' => 'Test',
                'GIT_COMMITTER_EMAIL' => 'test@example.com',
            ],
        );
        $process->mustRun();
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

/**
 * Non-capturing stub: returns a fixed section body regardless of
 * arguments, sufficient for prepend/replace/idempotence assertions.
 */
final class StubChangelogGenerator extends ChangelogGenerator
{
    public function __construct()
    {
        parent::__construct(new GitHubApi('test/test'));
    }

    public function generate(
        string $base,
        string $head,
        ?string $title = null,
        bool $includeGhsa = true,
        ?string $compareLinkOverride = null,
    ): string {
        $version = $title ?? 'unknown';
        $link = $compareLinkOverride ?? $head;
        return sprintf(
            "## [%s](https://github.com/openemr/openemr/compare/%s...%s) - 2026-07-12\n\n### Fixed\n\n  - stub body ([#42](https://github.com/openemr/openemr/pull/42))\n\n",
            $version,
            $base,
            $link,
        );
    }
}

/**
 * Mutable stub whose body can be swapped between calls -- lets tests
 * simulate the post-GHSA-publish amendment flow (release-time body ->
 * post-ghsa body -> back again) without spinning up the full
 * generator machinery.
 */
final class SwitchableChangelogGenerator extends ChangelogGenerator
{
    public string $body = '';

    public function __construct()
    {
        parent::__construct(new GitHubApi('test/test'));
    }

    public function generate(
        string $base,
        string $head,
        ?string $title = null,
        bool $includeGhsa = true,
        ?string $compareLinkOverride = null,
    ): string {
        return $this->body;
    }
}

/**
 * Capturing stub for asserting the arguments the mutator passes to
 * ChangelogGenerator::generate(). Returns a minimal body so the
 * prepend logic can still execute; assertions inspect the captured
 * fields on the returned object.
 */
final class CapturingChangelogGenerator extends ChangelogGenerator
{
    public string $base = '';
    public string $head = '';
    public ?string $title = null;
    public ?string $compareLinkOverride = null;

    public function __construct()
    {
        parent::__construct(new GitHubApi('test/test'));
    }

    public function generate(
        string $base,
        string $head,
        ?string $title = null,
        bool $includeGhsa = true,
        ?string $compareLinkOverride = null,
    ): string {
        $this->base = $base;
        $this->head = $head;
        $this->title = $title;
        $this->compareLinkOverride = $compareLinkOverride;
        return "## [{$title}](placeholder) - 2026-07-12\n\n### Fixed\n\n  - captured ([#1](https://x/1))\n\n";
    }
}
