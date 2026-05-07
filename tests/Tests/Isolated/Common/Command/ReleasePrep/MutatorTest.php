<?php

/**
 * Per-mutator tests for the openemr:release-prep conductor command.
 * Each test copies a fixture into a tmp project root, applies the
 * mutator, asserts the result matches the expected fixture exactly,
 * then re-applies the mutator and asserts the second run is a no-op.
 * Idempotence is the load-bearing property — the conductor workflow
 * runs these mutators on every push to a rel-* branch.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Common\Command\ReleasePrep;

use OpenEMR\Common\Command\ReleasePrep\Mutator\DockerComposeProductionMutator;
use OpenEMR\Common\Command\ReleasePrep\Mutator\DockerVersionFileMutator;
use OpenEMR\Common\Command\ReleasePrep\Mutator\GlobalsIncMutator;
use OpenEMR\Common\Command\ReleasePrep\Mutator\OpenApiVersionMutator;
use OpenEMR\Common\Command\ReleasePrep\Mutator\SqlUpgradeSkeletonMutator;
use OpenEMR\Common\Command\ReleasePrep\Mutator\SwaggerRegenMutator;
use OpenEMR\Common\Command\ReleasePrep\Mutator\VersionPhpMasterMutator;
use OpenEMR\Common\Command\ReleasePrep\Mutator\VersionPhpMutator;
use OpenEMR\Common\Command\ReleasePrep\MutatorContext;
use OpenEMR\Common\Command\ReleasePrep\MutatorInterface;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Process\Process;

#[Group('isolated')]
#[Group('release-prep')]
final class MutatorTest extends TestCase
{
    private const FIXTURE_DIR = __DIR__ . '/fixtures';

    private string $tmpDir = '';

    protected function setUp(): void
    {
        $this->tmpDir = sys_get_temp_dir() . '/openemr-release-prep-' . bin2hex(random_bytes(8));
        if (!mkdir($this->tmpDir, 0700, true)) {
            throw new \RuntimeException('Failed to create tmp dir: ' . $this->tmpDir);
        }
    }

    protected function tearDown(): void
    {
        $this->removeRecursive($this->tmpDir);
    }

    public function testVersionPhpStripsDevTagOnRel(): void
    {
        $this->copyFixture('version_php/dev_input.php', 'version.php');
        $context = MutatorContext::fromVersionString($this->tmpDir, '8.1.0');
        $this->assertMutationProducesAndIdempotent(
            new VersionPhpMutator(),
            $context,
            'version.php',
            self::FIXTURE_DIR . '/version_php/rel_810_expected.php',
        );
    }

    public function testVersionPhpMasterBumpsVersion(): void
    {
        $this->copyFixture('version_php/rel_810_expected.php', 'version.php');
        $context = MutatorContext::fromVersionString($this->tmpDir, '8.1.1');
        $this->assertMutationProducesAndIdempotent(
            new VersionPhpMasterMutator(),
            $context,
            'version.php',
            self::FIXTURE_DIR . '/version_php/master_post_810_expected.php',
        );
    }

    public function testGlobalsIncSwitchesAllowDebugLanguageDefault(): void
    {
        $this->copyFixture('globals_inc/input.php', 'library/globals.inc.php');
        $context = MutatorContext::fromVersionString($this->tmpDir, '8.1.0');
        $this->assertMutationProducesAndIdempotent(
            new GlobalsIncMutator(),
            $context,
            'library/globals.inc.php',
            self::FIXTURE_DIR . '/globals_inc/expected.php',
        );
    }

    public function testDockerComposePinsTagOnly(): void
    {
        $this->copyFixture('docker_compose/latest_input.yml', 'docker/production/docker-compose.yml');
        $context = MutatorContext::fromVersionString($this->tmpDir, '8.1.0');
        $this->assertMutationProducesAndIdempotent(
            new DockerComposeProductionMutator(),
            $context,
            'docker/production/docker-compose.yml',
            self::FIXTURE_DIR . '/docker_compose/pinned_no_digest_expected.yml',
        );
    }

    public function testDockerComposePinsTagAndDigest(): void
    {
        $this->copyFixture('docker_compose/latest_input.yml', 'docker/production/docker-compose.yml');
        $digest = 'sha256:' . str_repeat('a', 64);
        $context = MutatorContext::fromVersionString($this->tmpDir, '8.1.0', $digest);
        $this->assertMutationProducesAndIdempotent(
            new DockerComposeProductionMutator(),
            $context,
            'docker/production/docker-compose.yml',
            self::FIXTURE_DIR . '/docker_compose/pinned_with_digest_expected.yml',
        );
    }

    public function testOpenApiVersionBumpsAttribute(): void
    {
        $this->copyFixture('openapi/input.php', 'src/RestControllers/OpenApi/OpenApiDefinitions.php');
        $context = MutatorContext::fromVersionString($this->tmpDir, '8.1.0');
        $this->assertMutationProducesAndIdempotent(
            new OpenApiVersionMutator(),
            $context,
            'src/RestControllers/OpenApi/OpenApiDefinitions.php',
            self::FIXTURE_DIR . '/openapi/expected.php',
        );
    }

    public function testDockerVersionSweepIncrementsAllFiles(): void
    {
        $this->writeFile('docker-version', "10\n");
        $this->writeFile('sites/default/docker-version', "10\n");
        // A nested location to prove the sweep, not a hardcoded list.
        $this->writeFile('sites/other/docker-version', "5\n");
        // Excluded dirs that shouldn't be picked up.
        $this->writeFile('vendor/some/pkg/docker-version', "999\n");
        $this->writeFile('node_modules/some/pkg/docker-version', "999\n");

        $context = MutatorContext::fromVersionString($this->tmpDir, '8.1.0');
        $result = (new DockerVersionFileMutator())->apply($context);

        self::assertCount(3, $result->changedFiles);
        self::assertSame("11\n", file_get_contents($this->tmpDir . '/docker-version'));
        self::assertSame("11\n", file_get_contents($this->tmpDir . '/sites/default/docker-version'));
        self::assertSame("6\n", file_get_contents($this->tmpDir . '/sites/other/docker-version'));
        self::assertSame("999\n", file_get_contents($this->tmpDir . '/vendor/some/pkg/docker-version'));
        self::assertSame("999\n", file_get_contents($this->tmpDir . '/node_modules/some/pkg/docker-version'));
    }

    public function testSqlUpgradeSkeletonScaffoldsHeaderOnly(): void
    {
        // Project layout: a version.php at 8.1.1 (post-810 cut) and
        // an existing 8_0_0-to-8_1_0_upgrade.sql to copy the header from.
        $this->writeFile('version.php', "<?php\n\$v_major='8';\n\$v_minor='1';\n\$v_patch='1';\n");
        $existing = "-- header line one\n-- header line two\n\n-- header line three\nINSERT INTO foo VALUES (1);\nINSERT INTO foo VALUES (2);\n";
        $this->writeFile('sql/8_0_0-to-8_1_0_upgrade.sql', $existing);

        $context = MutatorContext::fromVersionString($this->tmpDir, '8.1.2');
        $result = (new SqlUpgradeSkeletonMutator())->apply($context);

        self::assertSame(['sql/8_1_1-to-8_1_2_upgrade.sql'], $result->changedFiles);
        self::assertSame(
            "-- header line one\n-- header line two\n\n-- header line three\n",
            file_get_contents($this->tmpDir . '/sql/8_1_1-to-8_1_2_upgrade.sql'),
        );

        // Idempotence: running twice doesn't overwrite the scaffold.
        $secondResult = (new SqlUpgradeSkeletonMutator())->apply($context);
        self::assertFalse($secondResult->changed());
    }

    public function testSwaggerRegenInvokesConsoleSubprocess(): void
    {
        $this->writeFile('swagger/openemr-api.yaml', "openapi: 3.0.0\ninfo:\n  version: 8.0.1\n");

        $invocations = [];
        $runner = function (Process $process) use (&$invocations): int {
            $invocations[] = $process->getCommandLine();
            // Simulate the subprocess writing a new YAML.
            $target = $process->getWorkingDirectory() . '/swagger/openemr-api.yaml';
            file_put_contents($target, "openapi: 3.0.0\ninfo:\n  version: 8.1.0\n");
            return 0;
        };

        $mutator = new SwaggerRegenMutator($runner);
        $result = $mutator->apply(MutatorContext::fromVersionString($this->tmpDir, '8.1.0'));

        self::assertTrue($result->changed());
        self::assertCount(1, $invocations);
        self::assertStringContainsString('openemr:create-api-documentation', $invocations[0]);
        self::assertStringContainsString('--skip-globals', $invocations[0]);

        // Idempotence: running again with the same output is a no-op.
        $secondResult = $mutator->apply(MutatorContext::fromVersionString($this->tmpDir, '8.1.0'));
        self::assertFalse($secondResult->changed());
    }

    public function testSwaggerRegenFailsWhenSubprocessExitsNonZero(): void
    {
        $this->writeFile('swagger/openemr-api.yaml', "openapi: 3.0.0\n");
        $runner = static fn (Process $process): int => 7;
        $mutator = new SwaggerRegenMutator($runner);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessageMatches('/exited 7/');
        $mutator->apply(MutatorContext::fromVersionString($this->tmpDir, '8.1.0'));
    }

    private function assertMutationProducesAndIdempotent(
        MutatorInterface $mutator,
        MutatorContext $context,
        string $relativePath,
        string $expectedFixturePath,
    ): void {
        $first = $mutator->apply($context);
        self::assertTrue($first->changed(), $mutator->name() . ' should produce a diff on first run');
        self::assertSame(
            $this->readFile($expectedFixturePath),
            $this->readFile($this->tmpDir . '/' . $relativePath),
            $mutator->name() . ' produced unexpected output',
        );

        $second = $mutator->apply($context);
        self::assertFalse(
            $second->changed(),
            $mutator->name() . ' is not idempotent — second run produced a diff',
        );
    }

    private function copyFixture(string $fixturePath, string $relativeTarget): void
    {
        $this->writeFile($relativeTarget, $this->readFile(self::FIXTURE_DIR . '/' . $fixturePath));
    }

    private function writeFile(string $relativePath, string $contents): void
    {
        $absolute = $this->tmpDir . '/' . $relativePath;
        $dir = dirname($absolute);
        if (!is_dir($dir) && !mkdir($dir, 0700, true) && !is_dir($dir)) {
            throw new \RuntimeException('Failed to create dir ' . $dir);
        }
        if (file_put_contents($absolute, $contents) === false) {
            throw new \RuntimeException('Failed to write ' . $absolute);
        }
    }

    private function readFile(string $path): string
    {
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
