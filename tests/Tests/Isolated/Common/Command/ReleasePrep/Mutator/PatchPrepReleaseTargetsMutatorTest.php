<?php

/**
 * Tests for PatchPrepReleaseTargetsMutator: master-side patch-prep
 * mutator that inserts a new dev row for the rel branch and drops any
 * `unreleased: true` rows for the same branch. Comprehensively covers
 * fresh-add, idempotent re-run, with/without placeholder, and multi-
 * branch interleaving scenarios.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2026 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Common\Command\ReleasePrep\Mutator;

use OpenEMR\Common\Command\ReleasePrep\Mutator\PatchPrepReleaseTargetsMutator;
use OpenEMR\Common\Command\ReleasePrep\MutatorContext;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Yaml\Yaml;

#[Group('isolated')]
#[Group('release-prep')]
final class PatchPrepReleaseTargetsMutatorTest extends TestCase
{
    private const TARGET_RELATIVE = '.github/release-targets.yml';

    private string $tmpDir = '';

    protected function setUp(): void
    {
        $this->tmpDir = sys_get_temp_dir() . '/openemr-ppr-' . bin2hex(random_bytes(8));
        if (!mkdir($this->tmpDir . '/.github', 0700, true)) {
            throw new \RuntimeException('Failed to create tmp dir');
        }
    }

    protected function tearDown(): void
    {
        $this->removeRecursive($this->tmpDir);
    }

    public function testFreshAddInsertsRelRowAndDropsPlaceholder(): void
    {
        // BEFORE state: rel-810 just shipped 8.1.0 but the multi-row dev
        // pattern's placeholder is still present (covers the initial
        // case of the first real patch-prep on rel-810).
        $input = <<<'YAML'
        - branch: master
          docker_tags: 8.2.0,dev
          openemr_version_ref: master

        - branch: rel-810
          docker_tags: 8.1.1,next
          openemr_version_ref: rel-810

        # placeholder for the multi-row dev pattern
        - branch: rel-810
          docker_tags: 8.1.0
          openemr_version_ref: v8_1_0
          unreleased: true

        - branch: rel-800
          docker_tags: 8.0.0,latest
          openemr_version_ref: v8_0_0
        YAML;
        $this->writeTarget($input);

        // After 8.1.0 shipped, the existing rel-810 8.1.1,next row was
        // already there as part of the cycle. After 8.1.1 ships and we
        // enter 8.1.2-dev, patch-prep adds 8.1.2,next.
        // For the FIRST patch-prep, the workflow is: rel-810 entering
        // 8.1.1-dev (target=8.1.1, prev=8.1.0). The new row added would
        // be 8.1.1,next — same as the existing row already there. So
        // idempotency kicks in: no row added. The placeholder is what
        // gets dropped.
        $result = (new PatchPrepReleaseTargetsMutator())->apply(
            $this->context('rel-810', '8.1.1'),
        );
        self::assertTrue($result->changed());

        $parsed = Yaml::parse($this->readTarget());
        self::assertIsArray($parsed);
        $rel810Rows = $this->rowsForBranch($parsed, 'rel-810');
        self::assertCount(1, $rel810Rows, 'placeholder row should be dropped, only the dev row remains');
        self::assertSame('8.1.1,next', $rel810Rows[0]['docker_tags']);
        self::assertSame('rel-810', $rel810Rows[0]['openemr_version_ref']);
        self::assertArrayNotHasKey('unreleased', $rel810Rows[0]);
    }

    public function testAddsNewDevRowAfterPatchShipped(): void
    {
        // BEFORE state: 8.1.1 shipped (carries latest), no unreleased
        // placeholder. Entering 8.1.2-dev.
        $input = <<<'YAML'
        - branch: master
          docker_tags: 8.2.0,dev
          openemr_version_ref: master

        - branch: rel-810
          docker_tags: 8.1.1,latest
          openemr_version_ref: v8_1_1

        - branch: rel-800
          docker_tags: 8.0.0
          openemr_version_ref: v8_0_0
        YAML;
        $this->writeTarget($input);

        $result = (new PatchPrepReleaseTargetsMutator())->apply(
            $this->context('rel-810', '8.1.2'),
        );
        self::assertTrue($result->changed());

        $parsed = Yaml::parse($this->readTarget());
        self::assertIsArray($parsed);
        $rel810Rows = $this->rowsForBranch($parsed, 'rel-810');
        self::assertCount(2, $rel810Rows);
        $tags = [];
        foreach ($rel810Rows as $row) {
            $tag = $row['docker_tags'] ?? null;
            self::assertIsString($tag);
            $tags[] = $tag;
        }
        self::assertContains('8.1.2,next', $tags);
        self::assertContains('8.1.1,latest', $tags, 'the just-shipped patch row must remain untouched');
    }

    public function testIdempotentOnRerun(): void
    {
        $input = <<<'YAML'
        - branch: master
          docker_tags: 8.2.0,dev
          openemr_version_ref: master

        - branch: rel-810
          docker_tags: 8.1.1,latest
          openemr_version_ref: v8_1_1
        YAML;
        $this->writeTarget($input);
        $mutator = new PatchPrepReleaseTargetsMutator();
        $first = $mutator->apply($this->context('rel-810', '8.1.2'));
        self::assertTrue($first->changed());

        $second = $mutator->apply($this->context('rel-810', '8.1.2'));
        self::assertFalse($second->changed(), 'second run must be a no-op');
    }

    public function testNoopWhenDevRowAndNoPlaceholderAlreadyPresent(): void
    {
        $input = <<<'YAML'
        - branch: master
          docker_tags: 8.2.0,dev
          openemr_version_ref: master

        - branch: rel-810
          docker_tags: 8.1.2,next
          openemr_version_ref: rel-810

        - branch: rel-810
          docker_tags: 8.1.1,latest
          openemr_version_ref: v8_1_1
        YAML;
        $this->writeTarget($input);

        $result = (new PatchPrepReleaseTargetsMutator())->apply(
            $this->context('rel-810', '8.1.2'),
        );
        self::assertFalse($result->changed());
    }

    public function testOnlyDropsPlaceholdersForTargetBranch(): void
    {
        // rel-810 and rel-820 both have placeholders. patch-prep on
        // rel-810 must touch only the rel-810 placeholder.
        $input = <<<'YAML'
        - branch: master
          docker_tags: 8.3.0,dev
          openemr_version_ref: master

        - branch: rel-820
          docker_tags: 8.2.1,next
          openemr_version_ref: rel-820

        - branch: rel-820
          docker_tags: 8.2.0
          openemr_version_ref: v8_2_0
          unreleased: true

        - branch: rel-810
          docker_tags: 8.1.1,latest
          openemr_version_ref: v8_1_1

        - branch: rel-810
          docker_tags: 8.1.0
          openemr_version_ref: v8_1_0
          unreleased: true
        YAML;
        $this->writeTarget($input);

        (new PatchPrepReleaseTargetsMutator())->apply(
            $this->context('rel-810', '8.1.2'),
        );

        $parsed = Yaml::parse($this->readTarget());
        self::assertIsArray($parsed);
        $rel810 = $this->rowsForBranch($parsed, 'rel-810');
        $rel820 = $this->rowsForBranch($parsed, 'rel-820');
        // rel-810 placeholder must be gone; new 8.1.2 dev row present;
        // 8.1.1,latest preserved.
        self::assertCount(2, $rel810);
        foreach ($rel810 as $row) {
            self::assertArrayNotHasKey('unreleased', $row);
        }
        // rel-820 placeholder must remain (not our target).
        self::assertCount(2, $rel820);
        $rel820HasPlaceholder = false;
        foreach ($rel820 as $row) {
            if (($row['unreleased'] ?? null) === true) {
                $rel820HasPlaceholder = true;
            }
        }
        self::assertTrue($rel820HasPlaceholder, 'other rel branch placeholder must remain');
    }

    public function testInsertedRowPlacementIsBetweenMasterAndFirstRel(): void
    {
        // rel-820 is the target; placement should be after master,
        // before rel-810 (existing first rel-* row).
        $input = <<<'YAML'
        - branch: master
          docker_tags: 8.3.0,dev
          openemr_version_ref: master

        - branch: rel-810
          docker_tags: 8.1.2,latest
          openemr_version_ref: v8_1_2
        YAML;
        $this->writeTarget($input);

        (new PatchPrepReleaseTargetsMutator())->apply(
            $this->context('rel-820', '8.2.1'),
        );
        $output = $this->readTarget();
        $masterPos = strpos($output, '- branch: master');
        $rel820Pos = strpos($output, '- branch: rel-820');
        $rel810Pos = strpos($output, '- branch: rel-810');
        self::assertNotFalse($masterPos);
        self::assertNotFalse($rel820Pos);
        self::assertNotFalse($rel810Pos);
        self::assertLessThan($rel820Pos, $masterPos);
        self::assertLessThan($rel810Pos, $rel820Pos);
    }

    public function testPreservesComments(): void
    {
        $input = <<<'YAML'
        # Top-of-file comment about the source of truth.

        - branch: master
          # comment about master
          docker_tags: 8.2.0,dev
          openemr_version_ref: master

        - branch: rel-810
          docker_tags: 8.1.1,latest
          openemr_version_ref: v8_1_1
        YAML;
        $this->writeTarget($input);

        (new PatchPrepReleaseTargetsMutator())->apply(
            $this->context('rel-810', '8.1.2'),
        );
        $output = $this->readTarget();
        self::assertStringContainsString('# Top-of-file comment about the source of truth.', $output);
        self::assertStringContainsString('# comment about master', $output);
    }

    public function testRequiresRelBranchOnContext(): void
    {
        $input = "- branch: master\n  docker_tags: 8.2.0,dev\n  openemr_version_ref: master\n";
        $this->writeTarget($input);
        $context = MutatorContext::fromVersionString($this->tmpDir, '8.1.2');
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessageMatches('/--rel-branch/');
        (new PatchPrepReleaseTargetsMutator())->apply($context);
    }

    public function testProducedYamlParsesCleanly(): void
    {
        $input = <<<'YAML'
        - branch: master
          docker_tags: 8.2.0,dev
          openemr_version_ref: master

        - branch: rel-810
          docker_tags: 8.1.1,latest
          openemr_version_ref: v8_1_1
        YAML;
        $this->writeTarget($input);
        (new PatchPrepReleaseTargetsMutator())->apply(
            $this->context('rel-810', '8.1.2'),
        );
        $parsed = Yaml::parse($this->readTarget());
        self::assertIsArray($parsed);
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function rowsForBranch(mixed $parsed, string $branch): array
    {
        self::assertIsArray($parsed);
        $out = [];
        foreach ($parsed as $row) {
            self::assertIsArray($row);
            $typedRow = [];
            foreach ($row as $key => $value) {
                self::assertIsString($key);
                $typedRow[$key] = $value;
            }
            if (($typedRow['branch'] ?? null) === $branch) {
                $out[] = $typedRow;
            }
        }
        return $out;
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
