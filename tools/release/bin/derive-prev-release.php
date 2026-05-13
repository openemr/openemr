#!/usr/bin/env php
<?php

/**
 * Print the previous release version (the most recent v<MAJOR>_<MINOR>_<PATCH>
 * tag in the local checkout). Used by the conductor workflow to populate
 * the prev_release field of openemr-rel-cut / openemr-rel-update payloads.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

require dirname(__DIR__, 3) . '/vendor/autoload.php';

// OpenEMR\Release\ classes live under autoload-dev so composer-require-checker
// does not demand conductor-only deps in production. Anything invoking this
// script needs a `composer install` that includes dev dependencies.
if (!class_exists(\OpenEMR\Release\BranchVersionResolver::class)) {
    fwrite(
        STDERR,
        "OpenEMR\\Release\\ classes are not autoloadable; rerun composer install with dev dependencies.\n",
    );
    exit(2);
}

use OpenEMR\Release\BranchVersionResolver;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\SingleCommandApplication;

(new SingleCommandApplication())
    ->setName('derive-prev-release')
    ->setDescription('Print the most recent release version tagged in this repo')
    ->addArgument('target-version', InputArgument::REQUIRED, 'Target release version (MAJOR.MINOR.PATCH)')
    ->addOption(
        'repo-dir',
        null,
        InputOption::VALUE_REQUIRED,
        'Repo path (defaults to cwd)',
        getcwd() === false ? '.' : getcwd(),
    )
    ->setCode(function (InputInterface $input, OutputInterface $output): int {
        $target = $input->getArgument('target-version');
        $repoDir = $input->getOption('repo-dir');
        if (!is_string($target) || $target === '' || !is_string($repoDir) || $repoDir === '') {
            $output->writeln('<error>target-version and --repo-dir are required</error>');
            return 2;
        }
        try {
            $output->writeln((new BranchVersionResolver($repoDir))->previousRelease($target));
            return 0;
        } catch (\InvalidArgumentException $e) {
            $output->writeln('<error>' . $e->getMessage() . '</error>');
            return 1;
        }
    })
    ->run();
