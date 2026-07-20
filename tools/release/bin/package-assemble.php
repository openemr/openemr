#!/usr/bin/env php
<?php

/**
 * Build the full distribution tarball + zip for an official OpenEMR release.
 *
 * Thin CLI wrapper around OpenEMR\Release\PackageAssembler; see that class for
 * the build steps and rationale.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc.
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

require dirname(__DIR__, 3) . '/vendor/autoload.php';

use OpenEMR\Release\PackageAssembler;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\SingleCommandApplication;

(new SingleCommandApplication())
    ->setName('package-assemble')
    ->setDescription('Build the full distribution tarball + zip for an official release')
    // `--release-version`, not `--version`: Symfony Console reserves `--version`
    // as a global flag that prints the app name and exits 0 before the command
    // runs, so `--version=8.1.0` would silently no-op.
    ->addOption('release-version', null, InputOption::VALUE_REQUIRED, 'Release version (e.g., 8.1.0)')
    ->addOption('openemr-dir', null, InputOption::VALUE_REQUIRED, 'Path to the checked-out openemr release branch')
    ->addOption('output-dir', null, InputOption::VALUE_REQUIRED, 'Output directory', './release-output')
    ->setCode(function (InputInterface $input, OutputInterface $output): int {
        $versionOption = $input->getOption('release-version');
        $openemrDirOption = $input->getOption('openemr-dir');
        $outputDirOption = $input->getOption('output-dir');

        if (!is_string($versionOption) || $versionOption === '') {
            $output->writeln('<error>--release-version is required</error>');
            return 1;
        }
        // Parse at the boundary: the version string flows into staging
        // paths and temp-file names inside PackageAssembler (see the
        // `openemr-<version>` staging dir), so validate the shape here
        // rather than downstream. OpenEMR release versions are strict
        // N.N.N — dev/pre-release suffixes never reach this CLI.
        if (preg_match('/^\d+\.\d+\.\d+$/', $versionOption) !== 1) {
            $output->writeln("<error>--release-version must be N.N.N (got: {$versionOption})</error>");
            return 1;
        }
        if (!is_string($openemrDirOption) || $openemrDirOption === '') {
            $output->writeln('<error>--openemr-dir is required</error>');
            return 1;
        }
        $outputDir = is_string($outputDirOption) && $outputDirOption !== ''
            ? rtrim($outputDirOption, '/')
            : './release-output';

        return (new PackageAssembler(
            $versionOption,
            rtrim($openemrDirOption, '/'),
            $outputDir,
            $output,
        ))->assemble();
    })
    ->run();
