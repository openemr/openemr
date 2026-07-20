#!/usr/bin/env php
<?php

/**
 * Run pre-release checks: milestone completeness and unpublished GHSAs.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc.
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

require dirname(__DIR__, 3) . '/vendor/autoload.php';

use OpenEMR\Release\GitHubApi;
use OpenEMR\Release\PreflightChecker;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\SingleCommandApplication;

(new SingleCommandApplication())
    ->setName('preflight')
    ->setDescription('Run pre-release checks')
    ->addOption('milestone', 'm', InputOption::VALUE_REQUIRED, 'Milestone name')
    ->addOption('repo', 'r', InputOption::VALUE_REQUIRED, 'GitHub repo', 'openemr/openemr')
    ->addOption('skip-milestone', null, InputOption::VALUE_NONE, 'Skip milestone check')
    ->addOption('skip-ghsa', null, InputOption::VALUE_NONE, 'Skip GHSA check')
    ->setCode(function (InputInterface $input, OutputInterface $output): int {
        // Parse raw CLI input into narrowed types at the boundary rather
        // than @var-casting downstream; see PSR-based coding guidelines.
        $milestoneOption = $input->getOption('milestone');
        if ($milestoneOption !== null && !is_string($milestoneOption)) {
            $output->writeln('<error>--milestone must be a string</error>');
            return 1;
        }
        $milestone = $milestoneOption;

        $repoOption = $input->getOption('repo');
        if (!is_string($repoOption) || $repoOption === '') {
            $output->writeln('<error>--repo must be a non-empty string</error>');
            return 1;
        }
        $repo = $repoOption;

        $api = new GitHubApi($repo);
        $checker = new PreflightChecker($api, $repo);
        $failures = 0;

        $skipMilestone = (bool) $input->getOption('skip-milestone');
        $skipGhsa = (bool) $input->getOption('skip-ghsa');

        if (!$skipMilestone) {
            if ($milestone === null) {
                $output->writeln('<error>--milestone is required (or use --skip-milestone)</error>');
                return 1;
            }
            $failures += $checker->checkMilestone($milestone, $output);
        }

        if (!$skipGhsa) {
            $failures += $checker->checkGhsa($output);
        }

        return $failures > 0 ? 1 : 0;
    })
    ->run();
