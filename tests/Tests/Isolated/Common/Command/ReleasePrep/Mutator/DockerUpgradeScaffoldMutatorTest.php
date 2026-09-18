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

    public function testPriorOpenemrVersionDerivedFromSqlBridgeLeft(): void
    {
        // Happy-path branch-cut derivation: fsupgrade-12.sh's
        // priorOpenemrVersion is read from the highest LEFT version
        // among sql/*_upgrade.sql bridges — that's the last shipped
        // version. Seed a rel-850 cut fixture: `8_4_1-to-8_5_0_upgrade.sql`
        // is the latest bridge, so priorOpenemrVersion must resolve to
        // "8.4.1".
        //
        // Prior fsupgrade file's own priorOpenemrVersion marker (the
        // search anchor for the substitution) is whatever the prior
        // cycle wrote; here we set it to "8.4.0" (rel-840's shipped
        // version). The scaffold reads the marker from the file, so the
        // substitution succeeds regardless of what the marker value is,
        // provided the load-bearing schema (all five anchor patterns)
        // is intact.
        $priorAt840 = str_replace(
            ['priorOpenemrVersion="8.1.0"', 'From prior version 8.1.0'],
            ['priorOpenemrVersion="8.4.0"', 'From prior version 8.4.0'],
            $this->fixture('fsupgrade-11.sh'),
        );
        file_put_contents(
            $this->tmpDir . '/docker/release/upgrade/fsupgrade-11.sh',
            $priorAt840,
        );
        // Wipe the setUp's default sql bridge and seed rel-850 shape.
        foreach (glob($this->tmpDir . '/sql/*_upgrade.sql') ?: [] as $f) {
            unlink($f);
        }
        file_put_contents(
            $this->tmpDir . '/sql/8_4_1-to-8_5_0_upgrade.sql',
            "-- rel-850 bridge fixture\n",
        );

        (new DockerUpgradeScaffoldMutator())->apply(
            MutatorContext::fromVersionString($this->tmpDir, '8.5.0', 'rel-850', 'rel-840'),
        );
        $next = file_get_contents($this->tmpDir . '/docker/release/upgrade/fsupgrade-12.sh');
        self::assertIsString($next);
        self::assertStringContainsString('priorOpenemrVersion="8.4.1"', $next);
        self::assertStringNotContainsString('priorOpenemrVersion="8.5.0"', $next);
        self::assertStringContainsString('#  From prior version 8.4.1 ', $next);
    }

    public function testFromVersionOverridesTheSqlScan(): void
    {
        // Patch-prep supplies an explicit fromVersion via MutatorContext.
        // The mutator must use it verbatim as the last-shipped marker,
        // bypassing the sql scan.
        //
        // Target 8.1.2, fromVersion 8.1.1. sql/ has a bridge with a
        // DIFFERENT left (8.0.9 — deliberately below fromVersion) to
        // prove the mutator picks fromVersion, not the sql scan.
        foreach (glob($this->tmpDir . '/sql/*_upgrade.sql') ?: [] as $f) {
            unlink($f);
        }
        file_put_contents(
            $this->tmpDir . '/sql/8_0_9-to-8_1_0_upgrade.sql',
            "-- older bridge fixture\n",
        );

        (new DockerUpgradeScaffoldMutator())->apply(
            MutatorContext::fromVersionString(
                $this->tmpDir,
                '8.1.2',
                'rel-810',
                null,
                '8.1.1',
            ),
        );
        $next = file_get_contents($this->tmpDir . '/docker/release/upgrade/fsupgrade-12.sh');
        self::assertIsString($next);
        self::assertStringContainsString('priorOpenemrVersion="8.1.1"', $next);
        self::assertStringContainsString('#  From prior version 8.1.1 ', $next);
    }

    public function testThrowsWhenSqlScanYieldsVersionEqualToTarget(): void
    {
        // Simulates the bug shape: sql/ already contains a bridge whose
        // left equals the target version (as if the sibling
        // SqlUpgradeSkeletonMutator ran BEFORE us on master-side, or a
        // stale sql tree was checked out). Invariant fires with a
        // pointed message rather than emitting a corrupt fsupgrade.
        foreach (glob($this->tmpDir . '/sql/*_upgrade.sql') ?: [] as $f) {
            unlink($f);
        }
        file_put_contents(
            $this->tmpDir . '/sql/8_2_0-to-8_3_0_upgrade.sql',
            "-- shouldn't be here yet\n",
        );

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessageMatches(
            '/priorOpenemrVersion 8\.2\.0 must be strictly less than target 8\.2\.0.*SqlUpgradeSkeletonMutator ran before DockerUpgradeScaffoldMutator/s',
        );
        (new DockerUpgradeScaffoldMutator())->apply($this->context());
    }

    public function testThrowsWhenSqlScanYieldsVersionGreaterThanTarget(): void
    {
        // Extreme regression signal: sql/ already contains a bridge
        // with a left > target (e.g., a checkout from a much newer line).
        foreach (glob($this->tmpDir . '/sql/*_upgrade.sql') ?: [] as $f) {
            unlink($f);
        }
        file_put_contents(
            $this->tmpDir . '/sql/9_0_0-to-9_1_0_upgrade.sql',
            "-- future bridge\n",
        );

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessageMatches(
            '/priorOpenemrVersion 9\.0\.0 must be strictly less than target 8\.2\.0/',
        );
        (new DockerUpgradeScaffoldMutator())->apply($this->context());
    }

    public function testThrowsWhenSqlHasNoUpgradeBridge(): void
    {
        // Rel-side branch-cut in a hypothetical fully-empty sql/ dir:
        // the derivation can't complete; surface a clear message rather
        // than proceed with a bogus value.
        foreach (glob($this->tmpDir . '/sql/*_upgrade.sql') ?: [] as $f) {
            unlink($f);
        }

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessageMatches(
            '/Cannot derive priorOpenemrVersion.*no sql\/.*_upgrade\.sql files/',
        );
        (new DockerUpgradeScaffoldMutator())->apply($this->context());
    }

    public function testThrowsWhenPriorFsupgradeLacksPriorOpenemrVersionAnchor(): void
    {
        // The prior fsupgrade file must carry a `priorOpenemrVersion="X.Y.Z"`
        // marker — that's what the scaffold uses as the substitution
        // anchor to derive the new file's marker. Strip it and expect
        // a clear error rather than a silently corrupt output.
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
        $this->expectExceptionMessageMatches('/cannot find priorOpenemrVersion/');
        (new DockerUpgradeScaffoldMutator())->apply($this->context());
    }

    public function testThrowsWhenPriorFsupgradeLacksUpgradeNumberAnchor(): void
    {
        // The five-substitution schema is load-bearing. Strip the
        // "Upgrade number N" header and expect the substitution loop
        // to fail with a clear error.
        $malformed = str_replace(
            '# Upgrade number 11 for OpenEMR docker',
            '# header stripped',
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

    public function testNoopMasterSidePostScaffoldWithSqlSiblingOutputPresent(): void
    {
        // Master-side post-scaffold state: docker-version bumped to 12,
        // fsupgrade-12.sh exists carrying priorOpenemrVersion="8.1.1",
        // AND the sibling SqlUpgradeSkeletonMutator has run —
        // sql/8_2_0-to-8_3_0_upgrade.sql is present alongside the
        // pre-existing 8_1_1-to-8_2_0 bridge from setup. Idempotency
        // relies on the "pre-scaffold view" derivation filtering out
        // any sql bridge with left >= target (8.2.0), so 8.1.1 remains
        // the highest LEFT and matches fsupgrade-12.sh's marker.
        // Without that filter, the invariant would trip on the raw
        // sql scan yielding "8.2.0".
        file_put_contents($this->tmpDir . '/docker-version', "12\n");
        file_put_contents($this->tmpDir . '/docker/release/upgrade/docker-version', "12\n");
        file_put_contents($this->tmpDir . '/sites/default/docker-version', "12\n");
        file_put_contents(
            $this->tmpDir . '/docker/release/upgrade/fsupgrade-12.sh',
            "#!/bin/bash\n# Upgrade number 12 for OpenEMR docker\n"
            . 'priorOpenemrVersion="8.1.1"' . "\n"
            . "# body preserved from prior file\n",
        );
        // SqlUpgradeSkeletonMutator's output on master-side.
        file_put_contents(
            $this->tmpDir . '/sql/8_2_0-to-8_3_0_upgrade.sql',
            "-- master-side skeleton\n",
        );

        $result = (new DockerUpgradeScaffoldMutator())->apply($this->context());
        self::assertFalse($result->changed());
        // docker-version files left at 12 (no further bump).
        self::assertSame("12", trim(file_get_contents($this->tmpDir . '/docker-version') ?: ''));
    }

    public function testNoopRelSidePostScaffoldViaFsupgradeMarker(): void
    {
        // Rel-side post-scaffold state: there is no master-side sql
        // bridge signal to key off (SqlUpgradeSkeletonMutator doesn't
        // run on rel-side branch-cut). Instead, idempotency detects OUR
        // work by matching fsupgrade-(current).sh's markers against
        // what derivePriorOpenemrVersion produces RIGHT NOW: docker-version
        // is bumped to 12, fsupgrade-12.sh has Upgrade number 12 and
        // priorOpenemrVersion="8.1.1" (matches sql-scan of the setup
        // fixture's 8_1_1-to-8_2_0 bridge).
        file_put_contents($this->tmpDir . '/docker-version', "12\n");
        file_put_contents($this->tmpDir . '/docker/release/upgrade/docker-version', "12\n");
        file_put_contents($this->tmpDir . '/sites/default/docker-version', "12\n");
        file_put_contents(
            $this->tmpDir . '/docker/release/upgrade/fsupgrade-12.sh',
            "#!/bin/bash\n# Upgrade number 12 for OpenEMR docker\n"
            . 'priorOpenemrVersion="8.1.1"' . "\n"
            . "# body preserved from prior file\n",
        );

        $result = (new DockerUpgradeScaffoldMutator())->apply($this->context());
        self::assertFalse($result->changed());
    }

    public function testNotNoopWhenCurrentDockerVersionFileLacksOurCutMarker(): void
    {
        // If a partial state exists (docker-version bumped to 12 but
        // fsupgrade-12.sh lacks our cut's last-shipped marker — e.g.,
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
            . 'priorOpenemrVersion="8.0.5"' . "\n"
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
            $this->tmpDir . '/sql',
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
        // Seed sql/ with an 8_1_1-to-8_2_0_upgrade.sql bridge so the
        // mutator's sql-scan derivation resolves priorOpenemrVersion to
        // "8.1.1" (the last shipped version before the rel-820 cut).
        file_put_contents(
            $this->tmpDir . '/sql/8_1_1-to-8_2_0_upgrade.sql',
            "-- last shipped bridge fixture\n",
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
