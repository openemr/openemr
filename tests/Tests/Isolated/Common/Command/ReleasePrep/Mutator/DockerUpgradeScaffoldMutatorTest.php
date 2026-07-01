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

    public function testCreatesFsupgradeStub(): void
    {
        (new DockerUpgradeScaffoldMutator())->apply($this->context());

        $stub = file_get_contents($this->tmpDir . '/docker/release/upgrade/fsupgrade-12.sh');
        self::assertIsString($stub);
        self::assertStringContainsString('# Upgrade number 12 for OpenEMR docker', $stub);
        self::assertStringContainsString('priorOpenemrVersion="8.1.0"', $stub);
        self::assertStringContainsString('Start: Upgrade to docker-version 12', $stub);
        self::assertStringContainsString('TODO: fill in upgrade logic per-release', $stub);
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

    public function testPriorVersionDerivedFromTargetMinor(): void
    {
        // target=8.5.0 → prior=8.4.0 (minor-1).
        (new DockerUpgradeScaffoldMutator())->apply(
            MutatorContext::fromVersionString($this->tmpDir, '8.5.0', null, 'rel-850', 'rel-840'),
        );
        $stub = file_get_contents($this->tmpDir . '/docker/release/upgrade/fsupgrade-12.sh');
        self::assertIsString($stub);
        self::assertStringContainsString('priorOpenemrVersion="8.4.0"', $stub);
    }

    public function testNoopWhenAlreadyAtPostCutState(): void
    {
        // Simulate the post-first-run state: docker-version bumped to
        // 12 + fsupgrade-12.sh carrying this cut's prior version marker
        // (priorOpenemrVersion="8.1.0" for the rel-820 cut). The mutator
        // should recognise its own work and no-op cleanly.
        file_put_contents($this->tmpDir . '/docker-version', "12\n");
        file_put_contents($this->tmpDir . '/docker/release/upgrade/docker-version', "12\n");
        file_put_contents($this->tmpDir . '/sites/default/docker-version', "12\n");
        file_put_contents(
            $this->tmpDir . '/docker/release/upgrade/fsupgrade-12.sh',
            "#!/bin/bash\n# Upgrade number 12 for OpenEMR docker\n"
            . 'priorOpenemrVersion="8.1.0"' . "\n"
            . "# TODO: fill in upgrade logic per-release; see prior fsupgrade-*.sh for examples\n",
        );

        $result = (new DockerUpgradeScaffoldMutator())->apply($this->context());
        self::assertFalse($result->changed());
        // docker-version files left at 12 (no further bump).
        self::assertSame("12", trim(file_get_contents($this->tmpDir . '/docker-version') ?: ''));
    }

    public function testNotNoopWhenStubLacksOurCutMarker(): void
    {
        // If a partial state exists (docker-version bumped to 12 but
        // fsupgrade-12.sh lacks our cut's TODO marker — e.g., somebody
        // manually filled in the upgrade body before re-running), the
        // mutator should NOT no-op; it should attempt the work and let
        // the downstream Dockerfile anchor mismatch surface as a clear
        // error rather than silently passing through.
        file_put_contents($this->tmpDir . '/docker-version', "12\n");
        file_put_contents($this->tmpDir . '/docker/release/upgrade/docker-version', "12\n");
        file_put_contents($this->tmpDir . '/sites/default/docker-version', "12\n");
        file_put_contents(
            $this->tmpDir . '/docker/release/upgrade/fsupgrade-12.sh',
            "#!/bin/bash\n# Manually filled-in upgrade, not our stub\n"
            . 'priorOpenemrVersion="8.1.0"' . "\n"
            . "echo doing real work\n",
        );

        // The Dockerfile still references fsupgrade-11.sh as the latest
        // entry; bumping to 13 will fail the anchor check, surfacing the
        // inconsistent state.
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessageMatches('/COPY block does not contain expected anchor/');
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
        return MutatorContext::fromVersionString($this->tmpDir, '8.2.0', null, 'rel-820', 'rel-810');
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
