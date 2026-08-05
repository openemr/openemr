<?php

/**
 * Tests for PostReleaseTargetsMutator: the master-side release-time
 * mutator that pins the just-shipped rel branch's openemr_version_ref to
 * the new tag, shuffles `latest`/`next` slots across rows, and drops the
 * unreleased placeholder row.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2026 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Common\Command\ReleasePrep\Mutator;

use OpenEMR\Common\Command\ReleasePrep\Mutator\PostReleaseTargetsMutator;
use OpenEMR\Common\Command\ReleasePrep\MutatorContext;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Yaml\Yaml;

#[Group('isolated')]
#[Group('release-prep')]
final class PostReleaseTargetsMutatorTest extends TestCase
{
    private const FIXTURE_DIR = __DIR__ . '/../fixtures/release_targets';
    private const TARGET_RELATIVE = '.github/release-targets.yml';

    private string $tmpDir = '';

    protected function setUp(): void
    {
        $this->tmpDir = sys_get_temp_dir() . '/openemr-prt-' . bin2hex(random_bytes(8));
        if (!mkdir($this->tmpDir . '/.github', 0700, true)) {
            throw new \RuntimeException('Failed to create tmp dir: ' . $this->tmpDir);
        }
    }

    protected function tearDown(): void
    {
        $this->removeRecursive($this->tmpDir);
    }

    public function testCanonical811FromRel810AppliesAllThreeTransforms(): void
    {
        $this->copyFixture('canonical_input.yml');
        $context = MutatorContext::fromVersionString($this->tmpDir, '8.1.1', 'rel-810');

        $result = (new PostReleaseTargetsMutator())->apply($context);

        self::assertTrue($result->changed(), 'first run should mutate the canonical input');
        self::assertSame([self::TARGET_RELATIVE], $result->changedFiles);
        self::assertSame(
            $this->fixture('canonical_8_1_1_expected.yml'),
            $this->readTarget(),
            'canonical 8.1.1-from-rel-810 mutation does not match expected fixture',
        );
    }

    public function testCanonical811FromRel810IsIdempotent(): void
    {
        $this->copyFixture('canonical_input.yml');
        $context = MutatorContext::fromVersionString($this->tmpDir, '8.1.1', 'rel-810');

        $mutator = new PostReleaseTargetsMutator();
        $mutator->apply($context);
        $second = $mutator->apply($context);

        self::assertFalse($second->changed(), 'second run should be a no-op');
        self::assertSame(
            $this->fixture('canonical_8_1_1_expected.yml'),
            $this->readTarget(),
            'idempotent second run should leave output identical to expected',
        );
    }

    public function testMultiRowUnreleasedPlaceholderIsDropped(): void
    {
        $this->copyFixture('canonical_input.yml');
        // The canonical input already contains the unreleased placeholder
        // for rel-810. Verify dropping it leaves the file with no row
        // carrying `unreleased: true` for that branch.
        $context = MutatorContext::fromVersionString($this->tmpDir, '8.1.1', 'rel-810');
        (new PostReleaseTargetsMutator())->apply($context);

        $parsed = Yaml::parse($this->readTarget());
        self::assertIsArray($parsed);
        $rel810Rows = array_values(array_filter(
            $parsed,
            static fn (mixed $r): bool => is_array($r) && ($r['branch'] ?? null) === 'rel-810',
        ));
        self::assertCount(1, $rel810Rows, 'placeholder rel-810 row should be dropped');
        self::assertArrayNotHasKey('unreleased', $rel810Rows[0]);
    }

    public function testSingleRelBranchBaseCaseHasNoPriorLatestToDrop(): void
    {
        // Synthetic minimal fixture: only one rel branch, no prior latest
        // holder. Verifies that the "drop latest from other rows" step is
        // a no-op when no other row has it.
        $input = <<<'YAML'
- branch: master
  docker_tags: 8.2.0,dev
  openemr_version_ref: master

- branch: rel-810
  docker_tags: 8.1.1,next
  openemr_version_ref: rel-810
YAML;
        $this->writeTarget($input);
        $context = MutatorContext::fromVersionString($this->tmpDir, '8.1.1', 'rel-810');
        (new PostReleaseTargetsMutator())->apply($context);

        $expected = <<<'YAML'
- branch: master
  docker_tags: 8.2.0,dev,next
  openemr_version_ref: master

- branch: rel-810
  docker_tags: 8.1.1,latest
  openemr_version_ref: v8_1_1
YAML;
        self::assertSame($expected, $this->readTarget());
    }

    public function testCommentsArePreservedOnSlotShuffleRows(): void
    {
        $this->copyFixture('canonical_input.yml');
        $context = MutatorContext::fromVersionString($this->tmpDir, '8.1.1', 'rel-810');
        (new PostReleaseTargetsMutator())->apply($context);

        $output = $this->readTarget();
        // The master row carries a multi-line comment block explaining
        // the version bump cadence; the rel-800 row carries an inline
        // comment about the 8.0.0 pointer. Both should survive the
        // mutation untouched (only the values change).
        self::assertStringContainsString(
            "# 8.2.0 is the version master is currently developing (see version.php's",
            $output,
            "master row's leading comment block should survive the slot-shuffle edit",
        );
        self::assertStringContainsString(
            '# 8.0.0 is the floating "current latest 8.0.0.x" pointer',
            $output,
            "rel-800 row's inline comment should survive the latest-drop edit",
        );
        // The top-of-file documentation block (>90 lines) should also be
        // entirely untouched.
        self::assertStringStartsWith('# Source of truth for Docker release builds.', $output);
    }

    public function testNextOwnerSelectionPrefersNewerRelBranchOverMaster(): void
    {
        // When a newer rel branch exists (rel-820), shipping 8.1.1 from
        // rel-810 should promote `next` onto rel-820, NOT master.
        $input = <<<'YAML'
- branch: master
  docker_tags: 8.3.0,dev
  openemr_version_ref: master

- branch: rel-820
  docker_tags: 8.2.0
  openemr_version_ref: rel-820

- branch: rel-810
  docker_tags: 8.1.1,next
  openemr_version_ref: rel-810

- branch: rel-800
  docker_tags: 8.0.0,latest
  openemr_version_ref: v8_0_0
YAML;
        $this->writeTarget($input);
        $context = MutatorContext::fromVersionString($this->tmpDir, '8.1.1', 'rel-810');
        (new PostReleaseTargetsMutator())->apply($context);

        $parsed = Yaml::parse($this->readTarget());
        self::assertIsArray($parsed);
        $byBranch = [];
        foreach ($parsed as $row) {
            self::assertIsArray($row);
            self::assertArrayHasKey('branch', $row);
            $branch = $row['branch'];
            self::assertIsString($branch);
            $byBranch[$branch] = $row;
        }
        self::assertSame('8.3.0,dev', $byBranch['master']['docker_tags'], 'master should not acquire next when a newer rel branch exists');
        self::assertSame('8.2.0,next', $byBranch['rel-820']['docker_tags'], 'rel-820 should acquire next');
        self::assertSame('8.1.1,latest', $byBranch['rel-810']['docker_tags']);
        self::assertSame('v8_1_1', $byBranch['rel-810']['openemr_version_ref']);
        self::assertSame('8.0.0', $byBranch['rel-800']['docker_tags']);
    }

    public function testInlineCommentsOnScalarsArePreserved(): void
    {
        // The reader must not slurp inline `# ...` comments into scalar
        // values, and the rewriter must preserve any trailing inline
        // comment on the line it edits. Verifies both halves at once:
        // docker_tags carries an inline comment that should survive the
        // promote-to-latest edit; openemr_version_ref carries one that
        // should survive the pin edit.
        $input = <<<'YAML'
- branch: master
  docker_tags: 8.2.0,dev
  openemr_version_ref: master

- branch: rel-810
  docker_tags: 8.1.1,next  # comment here
  openemr_version_ref: rel-810  # tracks tip until release
YAML;
        $this->writeTarget($input);
        $context = MutatorContext::fromVersionString($this->tmpDir, '8.1.1', 'rel-810');
        (new PostReleaseTargetsMutator())->apply($context);

        $output = $this->readTarget();
        self::assertStringContainsString(
            '  docker_tags: 8.1.1,latest  # comment here',
            $output,
            'inline comment on docker_tags should survive the slot-shuffle edit',
        );
        self::assertStringContainsString(
            '  openemr_version_ref: v8_1_1  # tracks tip until release',
            $output,
            'inline comment on openemr_version_ref should survive the pin edit',
        );
        // YAML must still parse cleanly with the inline comments intact.
        $parsed = Yaml::parse($output);
        self::assertIsArray($parsed);
    }

    public function testLegacyRelBranchRel704Idempotency(): void
    {
        // Legacy rel-NMP shapes (e.g. rel-704) don't fit the modern
        // rel-NN0 regex. isVersionTagFor() must still treat v7_0_X as
        // the "active" tag for rel-704 so re-running on already-mutated
        // input is a no-op (idempotency requirement).
        $input = <<<'YAML'
- branch: master
  docker_tags: 8.2.0,dev,next
  openemr_version_ref: master

- branch: rel-704
  docker_tags: 7.0.4,latest
  openemr_version_ref: v7_0_4
YAML;
        $this->writeTarget($input);
        $context = MutatorContext::fromVersionString($this->tmpDir, '7.0.4', 'rel-704');

        $mutator = new PostReleaseTargetsMutator();
        $first = $mutator->apply($context);
        self::assertFalse(
            $first->changed(),
            'already-shipped rel-704 should be a no-op (active row recognised via v7_0_X tag)',
        );
        self::assertSame($input, $this->readTarget());
    }

    public function testSkipsWhenTargetRelBranchHasNoLiveRow(): void
    {
        // Premature-finalize scenario: release-finalize fires for a
        // rel-820 push before the paired branch-cut PR has landed the
        // rel-820 row in release-targets.yml. If the mutator proceeded,
        // the slot-shuffle step would drop `latest` from rel-810 without
        // a promotion target — leaving nobody holding `latest`. The
        // mutator must skip cleanly instead.
        $input = <<<'YAML'
- branch: master
  docker_tags: 8.2.0,dev,next
  openemr_version_ref: master

- branch: rel-810
  docker_tags: 8.1.0,latest
  openemr_version_ref: v8_1_0
YAML;
        $this->writeTarget($input);
        $context = MutatorContext::fromVersionString($this->tmpDir, '8.2.0', 'rel-820');

        $result = (new PostReleaseTargetsMutator())->apply($context);
        self::assertFalse($result->changed(), 'no diff when target rel branch has no live row');
        self::assertSame($input, $this->readTarget(), 'file must be untouched');
        self::assertNotEmpty($result->messages, 'skip should surface an informational message');
        self::assertStringContainsString('rel-820', $result->messages[0]);
        self::assertStringContainsString('no live row', $result->messages[0]);
    }

    public function testSkipsWhenOnlyRowForRelBranchIsUnreleasedPlaceholder(): void
    {
        // Edge case: a placeholder row for rel-820 exists (marked
        // `unreleased: true`) but no live row yet. The placeholder alone
        // shouldn't unblock the shuffle — same invalid-state hazard as
        // when no row exists at all.
        $input = <<<'YAML'
- branch: master
  docker_tags: 8.2.0,dev,next
  openemr_version_ref: master

- branch: rel-820
  docker_tags: 8.1.0,latest
  openemr_version_ref: v8_1_0
  unreleased: true

- branch: rel-810
  docker_tags: 8.1.0,latest
  openemr_version_ref: v8_1_0
YAML;
        $this->writeTarget($input);
        $context = MutatorContext::fromVersionString($this->tmpDir, '8.2.0', 'rel-820');

        $result = (new PostReleaseTargetsMutator())->apply($context);
        self::assertFalse($result->changed());
        self::assertSame($input, $this->readTarget());
    }

    public function testRequiresRelBranchOnContext(): void
    {
        $this->copyFixture('canonical_input.yml');
        $context = MutatorContext::fromVersionString($this->tmpDir, '8.1.1');

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessageMatches('/--rel-branch/');
        (new PostReleaseTargetsMutator())->apply($context);
    }

    public function testHistoricalSecondaryRelBranchRowIsNotRePinned(): void
    {
        // The multi-row dev pattern can leave a second rel-810 row pinned
        // to a historical tag (v8_1_0). The mutator must only pin the
        // row that's still tracking the branch tip, leaving the
        // historical row untouched.
        $input = <<<'YAML'
- branch: master
  docker_tags: 8.2.0,dev
  openemr_version_ref: master

- branch: rel-810
  docker_tags: 8.1.2,next
  openemr_version_ref: rel-810

- branch: rel-810
  docker_tags: 8.1.1
  openemr_version_ref: v8_1_1

- branch: rel-800
  docker_tags: 8.0.0,latest
  openemr_version_ref: v8_0_0
YAML;
        $this->writeTarget($input);
        $context = MutatorContext::fromVersionString($this->tmpDir, '8.1.2', 'rel-810');
        (new PostReleaseTargetsMutator())->apply($context);

        $parsed = Yaml::parse($this->readTarget());
        self::assertIsArray($parsed);
        // Should still have two rel-810 rows; the active one is now pinned
        // to v8_1_2, the historical one is still pinned to v8_1_1.
        $rel810Rows = array_values(array_filter(
            $parsed,
            static fn (mixed $r): bool => is_array($r) && ($r['branch'] ?? null) === 'rel-810',
        ));
        self::assertCount(2, $rel810Rows);
        $refs = array_map(static fn (array $r): mixed => $r['openemr_version_ref'], $rel810Rows);
        self::assertContains('v8_1_2', $refs, 'newly-shipped row pinned to v8_1_2');
        self::assertContains('v8_1_1', $refs, 'historical row pinned to v8_1_1 (unchanged)');
    }

    private function copyFixture(string $fixtureName): void
    {
        $this->writeTarget($this->fixture($fixtureName));
    }

    private function fixture(string $name): string
    {
        $path = self::FIXTURE_DIR . '/' . $name;
        $contents = file_get_contents($path);
        if ($contents === false) {
            throw new \RuntimeException('Cannot read fixture ' . $path);
        }
        return $contents;
    }

    private function writeTarget(string $contents): void
    {
        $path = $this->tmpDir . '/' . self::TARGET_RELATIVE;
        if (file_put_contents($path, $contents) === false) {
            throw new \RuntimeException('Cannot write ' . $path);
        }
    }

    private function readTarget(): string
    {
        $path = $this->tmpDir . '/' . self::TARGET_RELATIVE;
        $contents = file_get_contents($path);
        if ($contents === false) {
            throw new \RuntimeException('Cannot read ' . $path);
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
