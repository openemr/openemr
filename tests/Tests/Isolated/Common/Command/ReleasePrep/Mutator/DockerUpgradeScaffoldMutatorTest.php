<?php

/**
 * Tests for DockerUpgradeScaffoldMutator: the docker-version bump +
 * fsupgrade stub creation + Dockerfile manifest update used by branch-cut
 * automation (and shared between rel-side and master-side).
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2026 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Common\Command\ReleasePrep\Mutator;

use OpenEMR\Common\Command\ReleasePrep\Mutator\DockerUpgradeScaffoldMutator;
use OpenEMR\Common\Command\ReleasePrep\MutatorContext;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

#[Group('isolated')]
#[Group('release-prep')]
final class DockerUpgradeScaffoldMutatorTest extends TestCase
{
    private const FIXTURE_DIR = __DIR__ . '/../fixtures/docker_upgrade';

    private string $tmpDir = '';

    protected function setUp(): void
    {
        $this->tmpDir = sys_get_temp_dir() . '/openemr-dus-' . bin2hex(random_bytes(8));
        $this->setupCleanTree(11);
    }

    protected function tearDown(): void
    {
        $this->removeRecursive($this->tmpDir);
    }

    public function testBumpsAllThreeDockerVersionFiles(): void
    {
        $result = (new DockerUpgradeScaffoldMutator())->apply($this->context());
        self::assertTrue($result->changed());

        self::assertSame("12\n", file_get_contents($this->tmpDir . '/docker-version'));
        self::assertSame("12\n", file_get_contents($this->tmpDir . '/docker/release/upgrade/docker-version'));
        self::assertSame("12\n", file_get_contents($this->tmpDir . '/sites/default/docker-version'));
    }

    public function testCreatesFsupgradeByCopyingPriorInFull(): void
    {
        (new DockerUpgradeScaffoldMutator())->apply($this->context());

        $next = file_get_contents($this->tmpDir . '/docker/release/upgrade/fsupgrade-12.sh');
        self::assertIsString($next);
        // Byte-for-byte match against the expected fixture: the mutator
        // copies fsupgrade-11.sh in full and applies exactly the five
        // documented substitutions.
        self::assertSame(
            $this->fixture('fsupgrade-12.sh.expected'),
            $next,
        );
    }

    public function testFsupgradeCopyDiffsOnlyOnTheFiveSubstitutedLines(): void
    {
        // Concrete guard against silent body drift: the diff between the
        // prior file and the produced file should be exactly the five
        // documented substitutions.
        (new DockerUpgradeScaffoldMutator())->apply($this->context());

        $prior = $this->fixture('fsupgrade-11.sh');
        $next = file_get_contents($this->tmpDir . '/docker/release/upgrade/fsupgrade-12.sh');
        self::assertIsString($next);

        $priorLines = explode("\n", $prior);
        $nextLines = explode("\n", $next);
        self::assertCount(count($priorLines), $nextLines, 'file line count must match');

        $changedIndexes = [];
        foreach ($priorLines as $i => $priorLine) {
            if ($priorLine !== $nextLines[$i]) {
                $changedIndexes[] = $i;
            }
        }
        // Exactly five lines changed: header comment, from-prior-version
        // comment, priorOpenemrVersion="…", Start: echo, Completed: echo.
        self::assertCount(
            5,
            $changedIndexes,
            'expected exactly 5 substituted lines; got ' . implode(',', $changedIndexes),
        );
    }

    public function testExtendsDockerfileCopyAndChmodBlocks(): void
    {
        (new DockerUpgradeScaffoldMutator())->apply($this->context());

        $dockerfile = file_get_contents($this->tmpDir . '/docker/release/Dockerfile');
        self::assertSame(
            $this->fixture('Dockerfile.expected'),
            $dockerfile,
        );
    }

    public function testIsIdempotentOnSecondRun(): void
    {
        $mutator = new DockerUpgradeScaffoldMutator();
        $first = $mutator->apply($this->context());
        self::assertTrue($first->changed());

        $second = $mutator->apply($this->context());
        self::assertFalse($second->changed());
    }

    public function testThrowsOnDockerVersionDrift(): void
    {
        // Hand-edit one of the three to introduce drift.
        file_put_contents($this->tmpDir . '/sites/default/docker-version', "10\n");

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessageMatches('/docker-version files disagree/');
        (new DockerUpgradeScaffoldMutator())->apply($this->context());
    }

    public function testThrowsOnMalformedDockerVersion(): void
    {
        file_put_contents($this->tmpDir . '/docker-version', "garbage\n");

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessageMatches('/does not contain a bare integer/');
        (new DockerUpgradeScaffoldMutator())->apply($this->context());
    }

    public function testThrowsWhenDockerfileMissingCopyAnchor(): void
    {
        file_put_contents(
            $this->tmpDir . '/docker/release/Dockerfile',
            "ARG OPENEMR_VERSION=master\n# no COPY block here\n",
        );

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessageMatches('/COPY block does not contain expected anchor/');
        (new DockerUpgradeScaffoldMutator())->apply($this->context());
    }

    public function testAnchorSearchIsDerivedFromTargetMinor(): void
    {
        // For target=8.5.0, the mutator computes prev=8.4.0 (target minor-1)
        // and uses that as the substitution anchor. Simulate the on-tree
        // reality at rel-850's cut: fsupgrade-11.sh's priorOpenemrVersion is
        // 8.4.0 (rel-840's shipped version). The mutator produces
        // fsupgrade-12.sh with priorOpenemrVersion=8.5.0 (rel-850's shipped
        // version, i.e. the target-version).
        $priorAt840 = str_replace(
            ['priorOpenemrVersion="8.1.0"', 'From prior version 8.1.0'],
            ['priorOpenemrVersion="8.4.0"', 'From prior version 8.4.0'],
            $this->fixture('fsupgrade-11.sh'),
        );
        file_put_contents(
            $this->tmpDir . '/docker/release/upgrade/fsupgrade-11.sh',
            $priorAt840,
        );

        (new DockerUpgradeScaffoldMutator())->apply(
            MutatorContext::fromVersionString($this->tmpDir, '8.5.0', 'rel-850', 'rel-840'),
        );
        $next = file_get_contents($this->tmpDir . '/docker/release/upgrade/fsupgrade-12.sh');
        self::assertIsString($next);
        self::assertStringContainsString('priorOpenemrVersion="8.5.0"', $next);
        self::assertStringNotContainsString('priorOpenemrVersion="8.4.0"', $next);
    }

    public function testThrowsWhenPriorFsupgradeLacksExpectedAnchor(): void
    {
        // The prior fsupgrade file must contain all five anchor lines
        // for the substitution to succeed. Substitute one of them out
        // (the priorOpenemrVersion assignment) and expect a clear error
        // rather than a silently corrupt output.
        $malformed = str_replace(
            'priorOpenemrVersion="8.1.0"',
            '# priorOpenemrVersion removed',
            $this->fixture('fsupgrade-11.sh'),
        );
        file_put_contents(
            $this->tmpDir . '/docker/release/upgrade/fsupgrade-11.sh',
            $malformed,
        );

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessageMatches('/fsupgrade scaffold expected to substitute/');
        (new DockerUpgradeScaffoldMutator())->apply($this->context());
    }

    public function testNoopWhenAlreadyAtPostCutState(): void
    {
        // Simulate the post-first-run state: docker-version bumped to
        // 12 + fsupgrade-12.sh carrying this cut's target-version marker
        // (priorOpenemrVersion="8.2.0" for the rel-820 cut — the version
        // rel-820 is shipping and rel-830 would need to upgrade from).
        // The mutator should recognise its own work and no-op cleanly.
        file_put_contents($this->tmpDir . '/docker-version', "12\n");
        file_put_contents($this->tmpDir . '/docker/release/upgrade/docker-version', "12\n");
        file_put_contents($this->tmpDir . '/sites/default/docker-version', "12\n");
        file_put_contents(
            $this->tmpDir . '/docker/release/upgrade/fsupgrade-12.sh',
            "#!/bin/bash\n# Upgrade number 12 for OpenEMR docker\n"
            . 'priorOpenemrVersion="8.2.0"' . "\n"
            . "# body preserved from prior file\n",
        );

        $result = (new DockerUpgradeScaffoldMutator())->apply($this->context());
        self::assertFalse($result->changed());
        // docker-version files left at 12 (no further bump).
        self::assertSame("12", trim(file_get_contents($this->tmpDir . '/docker-version') ?: ''));
    }

    public function testNotNoopWhenCurrentDockerVersionFileLacksOurCutMarker(): void
    {
        // If a partial state exists (docker-version bumped to 12 but
        // fsupgrade-12.sh lacks our cut's target-version marker — e.g.,
        // somebody manually edited the priorOpenemrVersion value), the
        // mutator should NOT no-op; it should attempt the work and let
        // the downstream anchor mismatch surface as a clear error
        // rather than silently passing through. The prior-file anchor
        // check catches it first (fsupgrade-12.sh is malformed when
        // read as the "prior" file for the N=13 write).
        file_put_contents($this->tmpDir . '/docker-version', "12\n");
        file_put_contents($this->tmpDir . '/docker/release/upgrade/docker-version', "12\n");
        file_put_contents($this->tmpDir . '/sites/default/docker-version', "12\n");
        file_put_contents(
            $this->tmpDir . '/docker/release/upgrade/fsupgrade-12.sh',
            "#!/bin/bash\n# Upgrade number 12 for OpenEMR docker\n"
            . 'priorOpenemrVersion="8.1.0"' . "\n"
            . "echo doing real work\n",
        );

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessageMatches(
            '/fsupgrade scaffold expected to substitute|COPY block does not contain expected anchor/',
        );
        (new DockerUpgradeScaffoldMutator())->apply($this->context());
    }

    private function setupCleanTree(int $currentVersion): void
    {
        $dirs = [
            $this->tmpDir,
            $this->tmpDir . '/docker/release/upgrade',
            $this->tmpDir . '/sites/default',
        ];
        foreach ($dirs as $d) {
            if (!is_dir($d) && !mkdir($d, 0700, true)) {
                throw new \RuntimeException('Failed to create ' . $d);
            }
        }
        file_put_contents($this->tmpDir . '/docker-version', $currentVersion . "\n");
        file_put_contents($this->tmpDir . '/docker/release/upgrade/docker-version', $currentVersion . "\n");
        file_put_contents($this->tmpDir . '/sites/default/docker-version', $currentVersion . "\n");
        file_put_contents(
            $this->tmpDir . '/docker/release/upgrade/fsupgrade-' . $currentVersion . '.sh',
            $this->fixture('fsupgrade-' . $currentVersion . '.sh'),
        );
        file_put_contents(
            $this->tmpDir . '/docker/release/Dockerfile',
            $this->fixture('Dockerfile.input'),
        );
    }

    private function context(): MutatorContext
    {
        return MutatorContext::fromVersionString($this->tmpDir, '8.2.0', 'rel-820', 'rel-810');
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
