<?php

/**
 * Isolated test: the pre-commit workflow's SKIP list stays in sync with the
 * local hooks it is meant to skip.
 *
 * `.github/workflows/pre-commit.yml` runs every hook in
 * `.pre-commit-config.yaml` against all files, but the runner has no PHP,
 * Composer or Node toolchain. It compensates with a hardcoded `SKIP` env var
 * naming the local hooks that need one. Nothing kept that list in sync with
 * the config: add a `language: system` hook and forget the SKIP entry and the
 * workflow fails with no indication of why; conversely, skipping a hook that
 * needs nothing external silently drops it from CI.
 *
 * The invariant asserted here is that SKIP contains exactly the local hooks
 * whose `language` requires a toolchain the workflow does not install.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Ci;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Yaml\Yaml;

class PreCommitSkipListIsolatedTest extends TestCase
{
    private const REPO_ROOT = __DIR__ . '/../../../..';
    private const CONFIG_FILE = self::REPO_ROOT . '/.pre-commit-config.yaml';
    private const WORKFLOW_FILE = self::REPO_ROOT . '/.github/workflows/pre-commit.yml';

    private const PREK_ACTION = 'j178/prek-action';

    public function testSkipListExactlyCoversToolchainDependentLocalHooks(): void
    {
        $expected = array_keys(array_filter(
            $this->localHookLanguages(),
            SelfContainedHookLanguage::requiresToolchain(...)
        ));
        $actual = $this->skipList();
        sort($expected);
        sort($actual);

        $this->assertSame(
            $expected,
            $actual,
            "The SKIP list in .github/workflows/pre-commit.yml has drifted from the\n"
                . "local hooks in .pre-commit-config.yaml.\n"
                . "\n"
                . "Missing from SKIP: a local hook needs a toolchain the workflow does not\n"
                . "install, so the job will fail with a confusing error.\n"
                . "\n"
                . "Extra in SKIP: either the id is a typo/stale, or a hook that needs nothing\n"
                . "external is being skipped, silently dropping it from CI.\n"
                . 'Update whichever side is wrong.'
        );
    }

    /**
     * Guard against a vacuous pass. If the workflow or config is restructured
     * so the navigation below finds nothing, both sides of the comparison
     * above collapse to empty and the assertion succeeds for the wrong reason.
     */
    public function testHooksAndSkipListAreDiscoverable(): void
    {
        $this->assertNotEmpty(
            $this->localHookLanguages(),
            'Found no `repo: local` hooks in .pre-commit-config.yaml. The parsing in this '
                . 'test is out of date with the config layout.'
        );
        $this->assertNotEmpty(
            $this->skipList(),
            sprintf(
                'Found no SKIP entries on a `%s` step in .github/workflows/pre-commit.yml. '
                    . 'The parsing in this test is out of date with the workflow layout.',
                self::PREK_ACTION
            )
        );
    }

    /**
     * Map of local hook id to its declared `language`.
     *
     * @return array<string, string>
     */
    private function localHookLanguages(): array
    {
        $repos = self::parseYamlFile(self::CONFIG_FILE)['repos'] ?? null;
        $this->assertIsArray($repos, 'Expected a `repos` list in .pre-commit-config.yaml.');

        $languages = [];
        foreach ($repos as $repo) {
            if (!is_array($repo) || ($repo['repo'] ?? null) !== 'local') {
                continue;
            }
            $hooks = $repo['hooks'] ?? null;
            if (!is_array($hooks)) {
                continue;
            }
            foreach ($hooks as $hook) {
                if (!is_array($hook)) {
                    continue;
                }
                $id = $hook['id'] ?? null;
                $language = $hook['language'] ?? null;
                if (is_string($id) && is_string($language)) {
                    $languages[$id] = $language;
                }
            }
        }
        return $languages;
    }

    /**
     * Hook ids named by the `SKIP` env var on the workflow's prek step.
     *
     * @return list<string>
     */
    private function skipList(): array
    {
        $jobs = self::parseYamlFile(self::WORKFLOW_FILE)['jobs'] ?? null;
        $this->assertIsArray($jobs, 'Expected a `jobs` map in .github/workflows/pre-commit.yml.');

        $skipped = [];
        foreach ($jobs as $job) {
            if (!is_array($job)) {
                continue;
            }
            $steps = $job['steps'] ?? null;
            if (!is_array($steps)) {
                continue;
            }
            foreach ($steps as $step) {
                if (!is_array($step)) {
                    continue;
                }
                $uses = $step['uses'] ?? null;
                // Require the `@` so a similarly-named repository -- say
                // `j178/prek-action-fork` -- cannot supply the SKIP list.
                if (!is_string($uses) || !str_starts_with($uses, self::PREK_ACTION . '@')) {
                    continue;
                }
                $env = $step['env'] ?? null;
                $skip = is_array($env) ? ($env['SKIP'] ?? null) : null;
                if (!is_string($skip)) {
                    continue;
                }
                foreach (explode(',', $skip) as $id) {
                    $id = trim($id);
                    if ($id !== '') {
                        $skipped[] = $id;
                    }
                }
            }
        }
        return $skipped;
    }

    /**
     * @return array<mixed>
     */
    private static function parseYamlFile(string $path): array
    {
        $parsed = Yaml::parseFile($path);
        self::assertIsArray($parsed, sprintf('Expected %s to parse to a YAML mapping.', basename($path)));
        return $parsed;
    }
}
