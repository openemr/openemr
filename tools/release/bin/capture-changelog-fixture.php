#!/usr/bin/env php
<?php

/**
 * One-shot maintenance tool: capture live GitHub API state for a
 * version range and write JSON fixtures that ChangelogGeneratorFixture
 * Test replays through FakeGitHubApi.
 *
 * Usage:
 *   php tools/release/bin/capture-changelog-fixture.php \
 *       --base=v8_1_0 --head=rel-820 \
 *       --fixture-dir=tests/Tests/Isolated/Release/fixtures/8_2_0
 *
 * Fetches:
 *   * commits.json  -- list of SHAs from `<base>...<head>`
 *   * prs.json      -- per-SHA PR objects (number/title/labels/url/author),
 *                      deduped by PR number, in enumeration order
 *   * advisories.json -- published GHSAs at capture time
 *
 * Rerun any time the mutator's inputs or filter shape shifts (e.g.
 * `GitHubApi::prsForCommits()` starts returning a new field). Then
 * rerun expected.md generation via the accompanying fixture test's
 * update-mode.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
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
    ->addOption('repo', 'r', InputOption::VALUE_REQUIRED, 'GitHub repo (owner/name)', 'openemr/openemr')
    ->addOption('fixture-dir', 'f', InputOption::VALUE_REQUIRED, 'Fixture output directory')
    ->setCode(function (InputInterface $input, OutputInterface $output): int {
        $base = $input->getOption('base');
        $head = $input->getOption('head');
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
        if (!is_string($repo) || $repo === '') {
            fwrite(STDERR, "--repo is required\n");
            return 1;
        }
        if (!is_string($fixtureDir) || $fixtureDir === '') {
            fwrite(STDERR, "--fixture-dir is required\n");
            return 1;
        }

        if (!is_dir($fixtureDir) && !mkdir($fixtureDir, 0755, true) && !is_dir($fixtureDir)) {
            fwrite(STDERR, "Cannot create fixture dir: {$fixtureDir}\n");
            return 1;
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
        $output->writeln("  " . count($advisories) . " advisories total (filtered at replay time)");

        // Trim each advisory to the fields ChangelogGenerator actually
        // reads: ghsa_id / severity / summary / html_url (rendered into
        // the section) plus references[].url (matched against the SHA
        // + PR sets to decide inclusion). Full advisory objects include
        // ~30 unused fields and clock in at ~1MB total; the trimmed
        // shape stays well under the pre-commit large-file cap while
        // preserving byte-identical replay through matchAdvisories().
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
            ],
            $advisories,
        );

        // JSON_PRETTY_PRINT keeps diffs readable when regenerated;
        // JSON_UNESCAPED_SLASHES matches openemr's ksort-and-pretty
        // conventions elsewhere in tools/release.
        $flags = JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES;
        file_put_contents($fixtureDir . '/commits.json', json_encode($shas, $flags) . "\n");
        file_put_contents($fixtureDir . '/prs.json', json_encode($prs, $flags) . "\n");
        file_put_contents($fixtureDir . '/advisories.json', json_encode($slimAdvisories, $flags) . "\n");

        $output->writeln("Wrote fixtures to <info>{$fixtureDir}/</info>");
        return 0;
    })
    ->run();
