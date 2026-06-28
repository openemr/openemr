#!/usr/bin/env php
<?php

/**
 * Render the release-prep PR body by substituting <VERSION> in the
 * template at .github/PULL_REQUEST_TEMPLATE/release-prep.md.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

require dirname(__DIR__, 3) . '/vendor/autoload.php';

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\SingleCommandApplication;

(new SingleCommandApplication())
    ->setName('render-pr-body')
    ->setDescription('Render a release PR body (prep or finalize) for a given version')
    ->addArgument('version', InputArgument::REQUIRED, 'Release version (MAJOR.MINOR.PATCH)')
    ->addOption(
        'template',
        null,
        InputOption::VALUE_REQUIRED,
        'Template path relative to repo root',
        '.github/PULL_REQUEST_TEMPLATE/release-prep.md',
    )
    ->addOption(
        'repo-dir',
        null,
        InputOption::VALUE_REQUIRED,
        'Repo path (defaults to cwd)',
        getcwd() === false ? '.' : getcwd(),
    )
    ->addOption(
        'rel-branch',
        null,
        InputOption::VALUE_REQUIRED,
        'Rel branch identifier (e.g. rel-810). Substituted for <REL_BRANCH> in the template; required for the release-finalize template.',
    )
    ->setCode(function (InputInterface $input, OutputInterface $output): int {
        $version = $input->getArgument('version');
        $templateRel = $input->getOption('template');
        $repoDir = $input->getOption('repo-dir');
        $relBranch = $input->getOption('rel-branch');
        if (
            !is_string($version) || $version === ''
            || !is_string($templateRel) || $templateRel === ''
            || !is_string($repoDir) || $repoDir === ''
        ) {
            $output->writeln('<error>version, --template, and --repo-dir are required</error>');
            return 2;
        }
        $path = $repoDir . '/' . $templateRel;
        $template = file_get_contents($path);
        if ($template === false) {
            $output->writeln('<error>cannot read template at ' . $path . '</error>');
            return 1;
        }
        $rendered = str_replace('<VERSION>', $version, $template);
        if (is_string($relBranch) && $relBranch !== '') {
            $rendered = str_replace('<REL_BRANCH>', $relBranch, $rendered);
        }
        $output->write($rendered);
        return 0;
    })
    ->run();
