#!/usr/bin/env php
<?php

/**
 * Extract a single version's section from openemr's CHANGELOG.md.
 *
 * Reads the checked-out openemr repo's CHANGELOG.md, locates the
 * `## [MAJOR.MINOR.PATCH]` heading for the requested version, and
 * writes everything from that heading up to (but not including) the
 * next `## ` heading (or EOF) to the output path.
 *
 * Replaces the pre-PR-5 changelog generation + compatibility injection
 * pipeline (task release:changelog + task release:compatibility). Both
 * concerns are now baked into openemr's CHANGELOG.md by mutators on the
 * release-prep + release-finalize partner PRs (openemr/openemr#12925 +
 * openemr/openemr#12928), so the devops build only needs to extract the
 * pre-computed section for the GitHub Release body + release-notes
 * asset.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc.
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

require dirname(__DIR__, 3) . '/vendor/autoload.php';

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\SingleCommandApplication;

(new SingleCommandApplication())
    ->setName('extract-changelog-section')
    ->setDescription("Extract one version's section from openemr's CHANGELOG.md")
    ->addOption('release-version', null, InputOption::VALUE_REQUIRED, 'MAJOR.MINOR.PATCH version to extract')
    ->addOption('changelog', 'c', InputOption::VALUE_REQUIRED, "Path to openemr's CHANGELOG.md")
    ->addOption('output', 'o', InputOption::VALUE_REQUIRED, 'Output path for the extracted section')
    ->setCode(function (InputInterface $input, OutputInterface $output): int {
        $version = $input->getOption('release-version');
        $changelogPath = $input->getOption('changelog');
        $outputPath = $input->getOption('output');

        if (!is_string($version) || preg_match('/^\d+\.\d+\.\d+$/', $version) !== 1) {
            fwrite(STDERR, "--release-version must be MAJOR.MINOR.PATCH (got " . var_export($version, true) . ")\n");
            return 1;
        }
        if (!is_string($changelogPath) || !is_file($changelogPath)) {
            fwrite(STDERR, '--changelog file not found: ' . var_export($changelogPath, true) . "\n");
            return 1;
        }
        if (!is_string($outputPath) || $outputPath === '') {
            fwrite(STDERR, "--output is required\n");
            return 1;
        }

        $notes = file_get_contents($changelogPath);
        if ($notes === false) {
            fwrite(STDERR, "Cannot read: {$changelogPath}\n");
            return 1;
        }

        // Match from `## [<version>]` line to (but not including) next
        // `## ` heading of any kind, or EOF. Multiline + dotall for the
        // body span.
        $pattern = '/(^## \[' . preg_quote($version, '/') . '\][^\n]*\n.*?)(?=^## |\z)/ms';
        if (preg_match($pattern, $notes, $matches) !== 1) {
            fwrite(STDERR, "No `## [{$version}]` section found in {$changelogPath}\n");
            return 1;
        }
        $section = rtrim($matches[1], "\n") . "\n";

        $dir = dirname($outputPath);
        if (!is_dir($dir) && !mkdir($dir, 0755, true) && !is_dir($dir)) {
            fwrite(STDERR, "Cannot create output dir: {$dir}\n");
            return 1;
        }
        if (file_put_contents($outputPath, $section) === false) {
            fwrite(STDERR, "Cannot write: {$outputPath}\n");
            return 1;
        }

        $bytes = strlen($section);
        $output->writeln("Extracted <info>{$version}</info> section ({$bytes} bytes) to <info>{$outputPath}</info>");
        return 0;
    })
    ->run();
