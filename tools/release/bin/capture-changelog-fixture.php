#!/usr/bin/env php
<?php

/**
 * One-shot maintenance tool: capture live GitHub API state for a
 * version range and write JSON fixtures that ChangelogGeneratorFixture
 * Test replays through FakeGitHubApi.
 *
 * Usage:
 *   php tools/release/bin/capture-changelog-fixture.php \
 *       --base=v8_1_0 --head=v8_2_0 --target-version=8.2.0 \
 *       --fixture-dir=tests/Tests/Isolated/Release/fixtures/8_2_0
 *
 * Layout written:
 *   * commits.json          -- list of SHAs from `<base>...<head>`
 *                              (shared across fixture modes)
 *   * prs.json              -- per-SHA PR objects
 *                              (number/title/labels/url/author),
 *                              deduped by PR number, in enumeration
 *                              order (shared across fixture modes)
 *   * release-time/advisories.json
 *                           -- empty list; simulates the state at
 *                              release-prep merge time, when the
 *                              release's own GHSAs are still in draft
 *                              and no advisory in the published set
 *                              matches this release.
 *   * post-ghsa/advisories.json
 *                           -- published GHSAs at capture time,
 *                              filtered to only those whose
 *                              `vulnerabilities[].patched_versions`
 *                              exactly equals --target-version (the
 *                              documented convention -- see
 *                              RELEASE_PROCESS.md) or whose references
 *                              URL a SHA or PR in the release range.
 *                              Simulates the post-GHSA-publish
 *                              amendment dispatch state. Filtering at
 *                              capture keeps this stable as unrelated
 *                              GHSAs get published for later releases.
 *
 * Rerun any time the mutator's inputs or filter shape shifts (e.g.
 * `GitHubApi::prsForCommits()` starts returning a new field). Then
 * rerun expected.md generation via the accompanying fixture test's
 * update-mode.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2026 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

require dirname(__DIR__, 3) . '/vendor/autoload.php';

if (!class_exists(\OpenEMR\Release\GitHubApi::class)) {
    fwrite(STDERR, "OpenEMR\\Release\\ classes not autoloadable; rerun composer install with dev dependencies.\n");
    exit(2);
}

use OpenEMR\Release\GitHubApi;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\SingleCommandApplication;

(new SingleCommandApplication())
    ->setName('capture-changelog-fixture')
    ->setDescription('Capture live GH state for a version range into JSON fixtures')
    ->addOption('base', 'b', InputOption::VALUE_REQUIRED, 'Base ref (previous tag, e.g. v8_1_0)')
    ->addOption('head', null, InputOption::VALUE_REQUIRED, 'Head ref (rel branch or tag, e.g. rel-820)')
    ->addOption('target-version', 't', InputOption::VALUE_REQUIRED, 'Release version string (e.g. 8.2.0); matched against GHSA patched_versions')
    ->addOption('repo', 'r', InputOption::VALUE_REQUIRED, 'GitHub repo (owner/name)', 'openemr/openemr')
    ->addOption('fixture-dir', 'f', InputOption::VALUE_REQUIRED, 'Fixture output directory')
    ->setCode(function (InputInterface $input, OutputInterface $output): int {
        $base = $input->getOption('base');
        $head = $input->getOption('head');
        $targetVersion = $input->getOption('target-version');
        $repo = $input->getOption('repo');
        $fixtureDir = $input->getOption('fixture-dir');

        if (!is_string($base) || $base === '') {
            fwrite(STDERR, "--base is required\n");
            return 1;
        }
        if (!is_string($head) || $head === '') {
            fwrite(STDERR, "--head is required\n");
            return 1;
        }
        if (!is_string($targetVersion) || $targetVersion === '') {
            fwrite(STDERR, "--target-version is required\n");
            return 1;
        }
        if (!is_string($repo) || $repo === '') {
            fwrite(STDERR, "--repo is required\n");
            return 1;
        }
        if (!is_string($fixtureDir) || $fixtureDir === '') {
            fwrite(STDERR, "--fixture-dir is required\n");
            return 1;
        }

        $releaseTimeDir = $fixtureDir . '/release-time';
        $postGhsaDir = $fixtureDir . '/post-ghsa';
        foreach ([$fixtureDir, $releaseTimeDir, $postGhsaDir] as $dir) {
            if (!is_dir($dir) && !mkdir($dir, 0755, true) && !is_dir($dir)) {
                fwrite(STDERR, "Cannot create fixture dir: {$dir}\n");
                return 1;
            }
        }

        $api = new GitHubApi($repo);

        $output->writeln("Fetching commits {$base}...{$head}...");
        $shas = $api->commitsBetweenRefs($base, $head);
        $output->writeln("  " . count($shas) . " commits");

        $output->writeln("Fetching per-commit PRs (this walks each SHA)...");
        $prs = $api->prsForCommits($shas);
        $output->writeln("  " . count($prs) . " unique PRs");

        $output->writeln("Fetching published advisories...");
        $advisories = $api->publishedAdvisories();
        $output->writeln("  " . count($advisories) . " advisories total (state=published)");

        // Filter to advisories that actually belong to this release --
        // same logic ChangelogGenerator::advisoryMatchesRange() applies
        // at replay: an exact `vulnerabilities[].patched_versions` match
        // (the primary path -- see RELEASE_PROCESS.md for the convention)
        // OR one of the references URLs a commit/PR in the range.
        // Filtering at capture time keeps the post-ghsa fixture stable
        // as GHSAs for unrelated releases get published upstream.
        $shaLookup = array_flip($shas);
        $prLookup = array_flip(array_map(
            static fn (array $pr): int => $pr['number'],
            $prs,
        ));
        $matchingAdvisories = array_values(array_filter(
            $advisories,
            static function (array $advisory) use ($shaLookup, $prLookup, $targetVersion): bool {
                $vulnerabilities = is_array($advisory['vulnerabilities'] ?? null) ? $advisory['vulnerabilities'] : [];
                foreach ($vulnerabilities as $vulnerability) {
                    $patched = is_array($vulnerability) && is_string($vulnerability['patched_versions'] ?? null)
                        ? $vulnerability['patched_versions']
                        : '';
                    if ($patched === $targetVersion) {
                        return true;
                    }
                }
                $references = is_array($advisory['references'] ?? null) ? $advisory['references'] : [];
                foreach ($references as $ref) {
                    $url = is_array($ref) && is_string($ref['url'] ?? null) ? $ref['url'] : '';
                    if (preg_match('#/commit/([0-9a-f]{40})#i', $url, $m) === 1 && isset($shaLookup[$m[1]])) {
                        return true;
                    }
                    if (preg_match('#/pull/(\d+)#', $url, $m) === 1 && isset($prLookup[(int) $m[1]])) {
                        return true;
                    }
                }
                return false;
            },
        ));
        $output->writeln("  " . count($matchingAdvisories) . " match target-version {$targetVersion} (kept for post-ghsa fixture)");

        // Trim each advisory to the fields ChangelogGenerator actually
        // reads: ghsa_id / severity / summary / html_url (rendered into
        // the section) + references[].url (SHA/PR reference match path)
        // + vulnerabilities[].patched_versions (primary match path).
        // Full advisory objects include ~30 unused fields; the trimmed
        // shape stays small and preserves byte-identical replay.
        $slimAdvisories = array_map(
            static fn (array $advisory): array => [
                'ghsa_id' => $advisory['ghsa_id'] ?? '',
                'severity' => $advisory['severity'] ?? 'unknown',
                'summary' => $advisory['summary'] ?? '',
                'html_url' => $advisory['html_url'] ?? '',
                'references' => array_map(
                    static fn (mixed $ref): array => is_array($ref) ? ['url' => $ref['url'] ?? ''] : ['url' => ''],
                    is_array($advisory['references'] ?? null) ? $advisory['references'] : [],
                ),
                'vulnerabilities' => array_map(
                    static fn (mixed $v): array => [
                        'patched_versions' => is_array($v) && is_string($v['patched_versions'] ?? null)
                            ? $v['patched_versions']
                            : '',
                    ],
                    is_array($advisory['vulnerabilities'] ?? null) ? $advisory['vulnerabilities'] : [],
                ),
            ],
            $matchingAdvisories,
        );

        // JSON_PRETTY_PRINT keeps diffs readable when regenerated;
        // JSON_UNESCAPED_SLASHES matches openemr's ksort-and-pretty
        // conventions elsewhere in tools/release.
        $flags = JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES;
        file_put_contents($fixtureDir . '/commits.json', json_encode($shas, $flags) . "\n");
        file_put_contents($fixtureDir . '/prs.json', json_encode($prs, $flags) . "\n");
        // release-time snapshot: no advisory in the published set matches
        // this release yet (its own GHSAs are still draft). Empty is the
        // faithful state; any published advisories that don't match are
        // filtered out at replay anyway, so keeping `[]` here is byte-
        // equivalent to keeping every non-matching advisory and cheaper.
        file_put_contents($releaseTimeDir . '/advisories.json', json_encode([], $flags) . "\n");
        // post-ghsa amendment snapshot: only the matching entries the
        // rendered Security section would contain.
        file_put_contents($postGhsaDir . '/advisories.json', json_encode($slimAdvisories, $flags) . "\n");

        $output->writeln("Wrote fixtures to <info>{$fixtureDir}/</info> (release-time/, post-ghsa/)");
        return 0;
    })
    ->run();
