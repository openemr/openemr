<?php

/**
 * Tests for BranchCutReleaseTargetsMutator: the master-side branch-cut
 * mutator that inserts the new rel-NNN0 row, bumps the master row's
 * docker_tags (minor++ + drop `next` + keep `dev`), and removes all
 * `unreleased: true` rows uniformly (covers normal-cut + skip-line-cut).
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2026 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Common\Command\ReleasePrep\Mutator;

use OpenEMR\Common\Command\ReleasePrep\Mutator\BranchCutReleaseTargetsMutator;
use OpenEMR\Common\Command\ReleasePrep\MutatorContext;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Yaml\Yaml;

#[Group('isolated')]
#[Group('release-prep')]
final class BranchCutReleaseTargetsMutatorTest extends TestCase
{
    private const FIXTURE_DIR = __DIR__ . '/../fixtures/branch_cut_targets';
    private const TARGET_RELATIVE = '.github/release-targets.yml';

    private string $tmpDir = '';

    protected function setUp(): void
    {
        $this->tmpDir = sys_get_temp_dir() . '/openemr-bct-' . bin2hex(random_bytes(8));
        if (!mkdir($this->tmpDir . '/.github', 0700, true)) {
            throw new \RuntimeException('Failed to create tmp dir');
        }
    }

    protected function tearDown(): void
    {
        $this->removeRecursive($this->tmpDir);
    }

    public function testCanonicalRel820CutAppliesAllTransforms(): void
    {
        $this->copyFixture('canonical_input.yml');
        $result = (new BranchCutReleaseTargetsMutator())->apply(
            $this->context('rel-820', '8.2.0'),
        );
        self::assertTrue($result->changed());
        self::assertSame(
            $this->fixture('canonical_rel820_expected.yml'),
            $this->readTarget(),
        );
    }

    public function testCanonicalRel820CutIsIdempotent(): void
    {
        $this->copyFixture('canonical_input.yml');
        $mutator = new BranchCutReleaseTargetsMutator();
        $mutator->apply($this->context('rel-820', '8.2.0'));
        $second = $mutator->apply($this->context('rel-820', '8.2.0'));
        self::assertFalse($second->changed());
        self::assertSame(
            $this->fixture('canonical_rel820_expected.yml'),
            $this->readTarget(),
        );
    }

    public function testInsertedRowPlacementIsAfterMasterBeforeFirstRel(): void
    {
        $this->copyFixture('canonical_input.yml');
        (new BranchCutReleaseTargetsMutator())->apply(
            $this->context('rel-820', '8.2.0'),
        );

        $output = $this->readTarget();
        // rel-820 row must appear after the master row and before rel-810.
        $masterPos = strpos($output, '- branch: master');
        $rel820Pos = strpos($output, '- branch: rel-820');
        $rel810Pos = strpos($output, '- branch: rel-810');
        self::assertNotFalse($masterPos);
        self::assertNotFalse($rel820Pos);
        self::assertNotFalse($rel810Pos);
        self::assertLessThan($rel820Pos, $masterPos);
        self::assertLessThan($rel810Pos, $rel820Pos);
    }

    public function testSkipLineCutDropsUnreleasedRows(): void
    {
        // Skip-line scenario: rel-810 was flagged unreleased before
        // rel-820 was cut. Both rel-810 rows should disappear; master's
        // `next` (acquired interim) should drop; rel-820 acquires it.
        $input = <<<'YAML'
        - branch: master
          docker_tags: 8.2.0,dev,next
          openemr_version_ref: master

        - branch: rel-810
          docker_tags: 8.1.1,next
          openemr_version_ref: rel-810
          unreleased: true

        # Second rel-810 row, also skipped.
        - branch: rel-810
          docker_tags: 8.1.0
          openemr_version_ref: v8_1_0
          unreleased: true

        - branch: rel-800
          docker_tags: 8.0.0,latest
          openemr_version_ref: v8_0_0
        YAML;
        $this->writeTarget($input);
        (new BranchCutReleaseTargetsMutator())->apply(
            $this->context('rel-820', '8.2.0'),
        );

        $parsed = Yaml::parse($this->readTarget());
        self::assertIsArray($parsed);
        $byBranch = [];
        foreach ($parsed as $row) {
            self::assertIsArray($row);
            self::assertArrayHasKey('branch', $row);
            $branch = $row['branch'];
            self::assertIsString($branch);
            $byBranch[$branch][] = $row;
        }
        self::assertArrayNotHasKey('rel-810', $byBranch, 'all rel-810 rows should be dropped');
        self::assertArrayHasKey('rel-820', $byBranch);
        self::assertSame('8.2.0,next', $byBranch['rel-820'][0]['docker_tags']);
        self::assertSame('8.3.0,dev', $byBranch['master'][0]['docker_tags']);
    }

    public function testNormalCutPathBumpsMasterAndAddsRelRow(): void
    {
        $input = <<<'YAML'
        - branch: master
          docker_tags: 8.2.0,dev,next
          openemr_version_ref: master

        - branch: rel-810
          docker_tags: 8.1.1,latest
          openemr_version_ref: v8_1_1
        YAML;
        $this->writeTarget($input);
        (new BranchCutReleaseTargetsMutator())->apply(
            $this->context('rel-820', '8.2.0'),
        );

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
        self::assertSame('8.3.0,dev', $byBranch['master']['docker_tags']);
        self::assertSame('8.2.0,next', $byBranch['rel-820']['docker_tags']);
        self::assertSame('rel-820', $byBranch['rel-820']['openemr_version_ref']);
        self::assertSame('8.1.1,latest', $byBranch['rel-810']['docker_tags']);
    }

    public function testMasterWithoutNextStillBumpsMinor(): void
    {
        // Master may not yet have `next` if the prior release hasn't
        // shipped — but a branch-cut shouldn't fail. Verify minor still
        // bumps even when there's no `next` to drop.
        $input = <<<'YAML'
        - branch: master
          docker_tags: 8.2.0,dev
          openemr_version_ref: master

        - branch: rel-810
          docker_tags: 8.1.1,next
          openemr_version_ref: rel-810
        YAML;
        $this->writeTarget($input);
        (new BranchCutReleaseTargetsMutator())->apply(
            $this->context('rel-820', '8.2.0'),
        );

        $parsed = Yaml::parse($this->readTarget());
        self::assertIsArray($parsed);
        foreach ($parsed as $row) {
            self::assertIsArray($row);
            if ($row['branch'] === 'master') {
                self::assertSame('8.3.0,dev', $row['docker_tags']);
            }
        }
    }

    public function testCommentsArePreservedOnMasterBumpAndRelInsert(): void
    {
        $this->copyFixture('canonical_input.yml');
        (new BranchCutReleaseTargetsMutator())->apply(
            $this->context('rel-820', '8.2.0'),
        );

        $output = $this->readTarget();
        self::assertStringContainsString(
            '# 8.2.0 is the version master is currently developing',
            $output,
        );
        self::assertStringContainsString(
            '# 8.0.0 is the floating "current latest 8.0.0.x" pointer',
            $output,
        );
        // The leading "Source of truth" comment block should also be intact.
        self::assertStringStartsWith('# Source of truth for Docker release builds.', $output);
    }

    public function testIdempotencyOnRerunAfterRel820Cut(): void
    {
        // Run once → state should be the expected output. Run again → no-op.
        $this->writeTarget($this->fixture('canonical_rel820_expected.yml'));
        $result = (new BranchCutReleaseTargetsMutator())->apply(
            $this->context('rel-820', '8.2.0'),
        );
        self::assertFalse($result->changed());
    }

    public function testRequiresRelBranchOnContext(): void
    {
        $this->copyFixture('canonical_input.yml');
        $context = MutatorContext::fromVersionString($this->tmpDir, '8.2.0');
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessageMatches('/--rel-branch/');
        (new BranchCutReleaseTargetsMutator())->apply($context);
    }

    public function testNoopWhenRelRowAlreadyExists(): void
    {
        // Pre-existing rel-820 row + master already bumped.
        $input = <<<'YAML'
        - branch: master
          docker_tags: 8.3.0,dev
          openemr_version_ref: master

        - branch: rel-820
          docker_tags: 8.2.0,next
          openemr_version_ref: rel-820

        - branch: rel-810
          docker_tags: 8.1.1,latest
          openemr_version_ref: v8_1_1
        YAML;
        $this->writeTarget($input);
        $result = (new BranchCutReleaseTargetsMutator())->apply(
            $this->context('rel-820', '8.2.0'),
        );
        self::assertFalse($result->changed());
    }

    public function testProducedYamlParsesCleanly(): void
    {
        $this->copyFixture('canonical_input.yml');
        (new BranchCutReleaseTargetsMutator())->apply(
            $this->context('rel-820', '8.2.0'),
        );
        $parsed = Yaml::parse($this->readTarget());
        self::assertIsArray($parsed);
        // Must have at least master + rel-820 rows.
        $branches = array_map(static fn (mixed $r): mixed => is_array($r) ? $r['branch'] : null, $parsed);
        self::assertContains('master', $branches);
        self::assertContains('rel-820', $branches);
    }

    private function context(string $relBranch, string $targetVersion): MutatorContext
    {
        return MutatorContext::fromVersionString(
            $this->tmpDir,
            $targetVersion,
            null,
            $relBranch,
        );
    }

    private function copyFixture(string $name): void
    {
        $this->writeTarget($this->fixture($name));
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
            throw new \RuntimeException('Cannot write target file');
        }
    }

    private function readTarget(): string
    {
        $path = $this->tmpDir . '/' . self::TARGET_RELATIVE;
        $contents = file_get_contents($path);
        if ($contents === false) {
            throw new \RuntimeException('Cannot read target file');
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
