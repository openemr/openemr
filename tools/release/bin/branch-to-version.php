#!/usr/bin/env php
<?php

/**
 * Print the MAJOR.MINOR.PATCH version a rel-* branch represents.
 * Used by the conductor workflow to derive --target-version.
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
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\SingleCommandApplication;

(new SingleCommandApplication())
    ->setName('branch-to-version')
    ->setDescription('Translate a rel-* branch name to MAJOR.MINOR.PATCH')
    ->addArgument('branch', InputArgument::REQUIRED, 'Branch name (e.g. rel-810)')
    ->setCode(function (InputInterface $input, OutputInterface $output): int {
        $raw = $input->getArgument('branch');
        if (!is_string($raw) || $raw === '') {
            $output->writeln('<error>branch argument is required</error>');
            return 2;
        }
        try {
            $version = BranchVersionResolver::branchToVersion($raw);
        } catch (\InvalidArgumentException $e) {
            $output->writeln('<error>' . $e->getMessage() . '</error>');
            return 1;
        }
        $output->writeln($version);
        return 0;
    })
    ->run();
