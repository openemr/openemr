<?php

/**
 * Isolated tests for CompatibilityMutator.
 *
 * Each test spins up a temp git repo with a checked-in `ci/` scaffold
 * + a rel-820 branch, then invokes the real mutator against it. Uses
 * the real CompatibilityDeriver + Renderer (both are `final readonly`
 * so no mocking; the test fixture provides the inputs deriver reads).
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2026 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Release;

use OpenEMR\Common\Command\ReleasePrep\MutatorContext;
use OpenEMR\Release\Mutator\CompatibilityMutator;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Process\Process;

final class CompatibilityMutatorTest extends TestCase
{
    private string $tmpDir = '';

    protected function setUp(): void
    {
        $this->tmpDir = sys_get_temp_dir() . '/openemr-compat-mut-test-' . bin2hex(random_bytes(8));
        if (!mkdir($this->tmpDir, 0700, true)) {
            throw new \RuntimeException('Failed to create tmp dir: ' . $this->tmpDir);
        }
        $this->git(['init', '-q', '-b', 'main']);
    }

    protected function tearDown(): void
    {
        $this->removeRecursive($this->tmpDir);
    }

    public function testRelBranchRequired(): void
    {
        $this->seedCi(['nginx_82_1011' => 'mariadb:10.11.0']);
        file_put_contents($this->tmpDir . '/CHANGELOG.md', "# CHANGELOG.md\n\n");
        $this->commit();

        $context = MutatorContext::fromVersionString($this->tmpDir, '8.2.1');
        $mutator = new CompatibilityMutator();

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessageMatches('/relBranch is required/');
        $mutator->apply($context);
    }

    public function testInjectsMinimumSupportedVersionsAfterVersionHeading(): void
    {
        $this->seedCi([
            'nginx_82_1011' => 'mariadb:10.11.0',
            'nginx_83_1108' => 'mariadb:11.8.6',
            'nginx_82_57'     => 'mysql:5.7.44',
        ]);
        $this->seedChangelog();
        $this->commit();
        $this->git(['branch', 'rel-820']);

        $result = $this->apply();
        self::assertTrue($result->changed());
        $updated = (string) file_get_contents($this->tmpDir . '/CHANGELOG.md');

        self::assertStringContainsString('### Minimum supported versions', $updated);
        // Minimum of {8.2, 8.3} = 8.2 (php)
        self::assertStringContainsString('- **PHP** 8.2+', $updated);
        // Minimum of {10.11, 11.8} = 10.11 (mariadb)
        self::assertStringContainsString('- **MariaDB** 10.11+', $updated);
        // Only one mysql entry → 5.7
        self::assertStringContainsString('- **MySQL** 5.7+', $updated);
        self::assertLessThan(
            strpos($updated, '### Fixed'),
            strpos($updated, '### Minimum supported versions'),
            'compat block appears before Fixed',
        );
    }

    public function testMatrixUrlUsesRelBranch(): void
    {
        $this->seedCi(['nginx_82_1011' => 'mariadb:10.11.0']);
        $this->seedChangelog();
        $this->commit();
        $this->git(['branch', 'rel-820']);

        $this->apply();
        $updated = (string) file_get_contents($this->tmpDir . '/CHANGELOG.md');

        self::assertStringContainsString(
            '[tested CI matrix](https://github.com/openemr/openemr/tree/rel-820/ci)',
            $updated,
        );
    }

    public function testRerunIsNoOpWhenSameCiState(): void
    {
        $this->seedCi(['nginx_82_1011' => 'mariadb:10.11.0']);
        $this->seedChangelog();
        $this->commit();
        $this->git(['branch', 'rel-820']);

        $first = $this->apply();
        self::assertTrue($first->changed());

        $second = $this->apply();
        self::assertFalse($second->changed(), 'idempotent rerun');
    }

    public function testResultShapeCarriesChangelogPathAndDerivedMinimums(): void
    {
        $this->seedCi(['nginx_82_1011' => 'mariadb:10.11.0']);
        $this->seedChangelog();
        $this->commit();
        $this->git(['branch', 'rel-820']);

        $result = $this->apply();
        self::assertSame(['CHANGELOG.md'], $result->changedFiles);
        self::assertCount(1, $result->messages);
        self::assertStringContainsString('rel-820', $result->messages[0]);
        self::assertStringContainsString('php=8.2', $result->messages[0]);
        self::assertStringContainsString('mariadb=10.11', $result->messages[0]);
    }

    public function testResolvesRelBranchViaOriginRemoteTrackingRefWhenLocalBranchAbsent(): void
    {
        // Simulate the master-scope workflow shape: a separate
        // `master-checkout` cloned with --branch master --single-branch.
        // The rel-820 branch is not locally reachable; only origin/rel-820
        // exists as a remote-tracking ref.
        $this->seedCi(['nginx_82_1011' => 'mariadb:10.11.0']);
        $this->seedChangelog();
        $this->commit();
        $this->git(['branch', 'rel-820']);

        // Set up a bare "origin" and clone --single-branch master
        $originDir = sys_get_temp_dir() . '/openemr-origin-' . bin2hex(random_bytes(6));
        $checkoutDir = sys_get_temp_dir() . '/openemr-master-co-' . bin2hex(random_bytes(6));
        try {
            $this->git(['clone', '--quiet', '--bare', $this->tmpDir, $originDir]);
            $this->git([
                'clone', '--quiet',
                '--branch', 'main', '--single-branch',
                $originDir, $checkoutDir,
            ]);

            // Verify pre-conditions: rel-820 is NOT a local branch,
            // but origin/rel-820 is a remote-tracking ref. With
            // --single-branch we need an explicit refspec to populate
            // refs/remotes/origin/rel-820 (bare `git fetch origin rel-820`
            // would only update FETCH_HEAD).
            $this->git(['fetch', '--quiet', 'origin', 'rel-820:refs/remotes/origin/rel-820'], $checkoutDir);
            // Inline Process for this one call because it expects failure
            // (probeLocal->run() != mustRun()); helper is mustRun-only.
            // Env still hermetic via self::gitEnv().
            $probeLocal = new Process(['git', 'rev-parse', '--verify', '--quiet', 'refs/heads/rel-820'], $checkoutDir, self::gitEnv());
            $probeLocal->run();
            self::assertFalse($probeLocal->isSuccessful(), 'rel-820 must not be a local branch in the master-style checkout');
            $this->git(['rev-parse', '--verify', '--quiet', 'refs/remotes/origin/rel-820'], $checkoutDir);

            file_put_contents($checkoutDir . '/CHANGELOG.md', <<<'MD'
# CHANGELOG.md

## [8.2.1](https://github.com/openemr/openemr/compare/v8_2_0...v8_2_1) - 2026-08-01

### Fixed

  - new bugfix ([#1](https://github.com/openemr/openemr/pull/1))

MD);
            $context = MutatorContext::fromVersionString($checkoutDir, '8.2.1', 'rel-820');
            $result = (new CompatibilityMutator())->apply($context);

            self::assertTrue($result->changed());
            $updated = (string) file_get_contents($checkoutDir . '/CHANGELOG.md');
            self::assertStringContainsString('- **MariaDB** 10.11+', $updated);
        } finally {
            $this->removeRecursive($checkoutDir);
            $this->removeRecursive($originDir);
        }
    }

    public function testResolveRelBranchThrowsWhenNeitherLocalNorRemoteRefExists(): void
    {
        $this->seedCi(['nginx_82_1011' => 'mariadb:10.11.0']);
        $this->seedChangelog();
        $this->commit();
        // rel-820 NOT created

        $context = MutatorContext::fromVersionString($this->tmpDir, '8.2.1', 'rel-820');

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessageMatches('/neither rel-820 nor origin\/rel-820/');
        (new CompatibilityMutator())->apply($context);
    }

    public function testInjectPreservesOlderReleasesCompatBlock(): void
    {
        // Multi-heading CHANGELOG: prior release already has its own compat
        // block. The mutator should inject into the NEW top section and
        // leave the older release's block intact.
        $this->seedCi(['nginx_82_1011' => 'mariadb:10.11.0']);
        file_put_contents($this->tmpDir . '/CHANGELOG.md', <<<'MD'
# CHANGELOG.md

## [8.2.1](https://github.com/openemr/openemr/compare/v8_2_0...v8_2_1) - 2026-08-01

### Fixed

  - new bugfix ([#200](https://github.com/openemr/openemr/pull/200))

## [8.2.0](https://github.com/openemr/openemr/compare/v8_1_0...v8_2_0) - 2026-07-08

### Minimum supported versions

- **PHP** 8.2+

See the [tested CI matrix](https://github.com/openemr/openemr/tree/rel-820/ci) for all tested version combinations.

### Fixed

  - old bugfix ([#100](https://github.com/openemr/openemr/pull/100))

MD);
        $this->commit();
        $this->git(['branch', 'rel-820']);

        $this->apply();
        $updated = (string) file_get_contents($this->tmpDir . '/CHANGELOG.md');

        // 8.2.1 gets its new compat block (mariadb 10.11 from seedCi)
        self::assertStringContainsString('- **MariaDB** 10.11+', $updated);
        // 8.2.0's original compat block is untouched (still PHP 8.2 + no MariaDB entry)
        self::assertMatchesRegularExpression(
            '/## \[8\.2\.0\].*?### Minimum supported versions.*?- \*\*PHP\*\* 8\.2\+/s',
            $updated,
        );
        // Exactly two compat sections now (one per release)
        self::assertSame(
            2,
            substr_count($updated, '### Minimum supported versions'),
            'top section gets new compat block, older section keeps its own',
        );
    }

    public function testMasterScopeExtractsRelBranchCiNotMasterCi(): void
    {
        // rel-820 has PHP 8.2 in its ci/; master has drifted to PHP 8.3.
        // Mutator on master scope must derive from rel-820's ci/, not master's.
        $this->seedCi(['nginx_82_1011' => 'mariadb:10.11.0']);
        $this->seedChangelog();
        $this->commit();
        $this->git(['branch', 'rel-820']);

        // Drift master's ci/ to a newer PHP
        $this->removeRecursive($this->tmpDir . '/ci');
        $this->seedCi(['nginx_83_1108' => 'mariadb:11.8.6']);
        $this->git(['add', '.']);
        $this->git(['commit', '-q', '-m', 'master drift']);
        // Now master has php83, rel-820 has php82

        // apply() runs against master's checkout, but passes relBranch=rel-820
        $result = $this->apply();
        self::assertTrue($result->changed());
        $updated = (string) file_get_contents($this->tmpDir . '/CHANGELOG.md');

        // Should reflect rel-820's ci/ (PHP 8.2), NOT master's ci/ (PHP 8.3)
        self::assertStringContainsString('- **PHP** 8.2+', $updated);
        self::assertStringNotContainsString('- **PHP** 8.3+', $updated);
    }

    /**
     * @param array<string, string> $dirs Map of ci-subdir → mysql-image value
     */
    private function seedCi(array $dirs): void
    {
        mkdir($this->tmpDir . '/ci', 0700, true);
        foreach ($dirs as $subdir => $image) {
            $path = $this->tmpDir . '/ci/' . $subdir;
            mkdir($path, 0700, true);
            file_put_contents(
                $path . '/docker-compose.yml',
                "services:\n  mysql:\n    image: {$image}\n",
            );
        }
    }

    private function seedChangelog(): void
    {
        file_put_contents($this->tmpDir . '/CHANGELOG.md', <<<'MD'
# CHANGELOG.md

## [8.2.1](https://github.com/openemr/openemr/compare/v8_2_0...v8_2_1) - 2026-08-01

### Fixed

  - stuff ([#1](https://github.com/openemr/openemr/pull/1))

MD);
    }

    private function commit(): void
    {
        $this->git(['add', '.']);
        $this->git(['commit', '-q', '-m', 'seed']);
    }

    private function apply(): \OpenEMR\Common\Command\ReleasePrep\MutatorResult
    {
        $context = MutatorContext::fromVersionString($this->tmpDir, '8.2.1', 'rel-820');
        $mutator = new CompatibilityMutator();
        return $mutator->apply($context);
    }

    /**
     * @param list<string> $args
     */
    private function git(array $args, ?string $cwd = null): void
    {
        $process = new Process(
            ['git', ...$args],
            $cwd ?? $this->tmpDir,
            self::gitEnv(),
        );
        $process->mustRun();
    }

    /**
     * Hermetic git env: null the developer's ~/.gitconfig + system config
     * so options like `tag.gpgsign=true` don't promote our lightweight
     * tags to signed annotated tags (which then demand a message and fail
     * with exit 128). CI is unaffected but local runs bite developers who
     * sign by default. Also blocks any other future ~/.gitconfig drift
     * (init.defaultBranch, fetch.parallel, core.pager, etc.) from making
     * this suite non-reproducible. See openemr/openemr#13018.
     *
     * @return array<string, string>
     */
    private static function gitEnv(): array
    {
        return [
            'GIT_CONFIG_GLOBAL' => '/dev/null',
            'GIT_CONFIG_SYSTEM' => '/dev/null',
            'GIT_AUTHOR_NAME' => 'Test',
            'GIT_AUTHOR_EMAIL' => 'test@example.com',
            'GIT_COMMITTER_NAME' => 'Test',
            'GIT_COMMITTER_EMAIL' => 'test@example.com',
        ];
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
