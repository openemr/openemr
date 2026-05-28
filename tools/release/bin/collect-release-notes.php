#!/usr/bin/env php
<?php

/**
 * Emit the release-notes JSON manifest the release-prep conductor
 * passes to openemr:release-prep --release-notes-json=. Shells out to
 * `gh` for milestone lookup and PR enumeration; writes the result to
 * stdout so the workflow can redirect it to a tmp file.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

require dirname(__DIR__, 3) . '/vendor/autoload.php';

if (!class_exists(\OpenEMR\Release\ReleaseNotesCollector::class)) {
    fwrite(
        STDERR,
        "OpenEMR\\Release\\ classes are not autoloadable; rerun composer install with dev dependencies.\n",
    );
    exit(2);
}

use OpenEMR\Release\ReleaseNotesCollector;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\SingleCommandApplication;

(new SingleCommandApplication())
    ->setName('collect-release-notes')
    ->setDescription('Emit the release-notes JSON manifest for openemr:release-prep')
    ->addOption(
        'target-version',
        null,
        InputOption::VALUE_REQUIRED,
        'Target release version (MAJOR.MINOR.PATCH)',
    )
    ->addOption(
        'repo',
        null,
        InputOption::VALUE_REQUIRED,
        'GitHub repo (owner/name)',
        'openemr/openemr',
    )
    ->addOption(
        'date',
        null,
        InputOption::VALUE_REQUIRED,
        'Release date (YYYY-MM-DD). Defaults to today UTC.',
    )
    ->setCode(function (InputInterface $input, OutputInterface $output): int {
        $version = $input->getOption('target-version');
        $repo = $input->getOption('repo');
        $date = $input->getOption('date');
        if (!is_string($version) || $version === '') {
            $output->writeln('<error>--target-version is required</error>');
            return 2;
        }
        if (!is_string($repo) || $repo === '') {
            $output->writeln('<error>--repo is required</error>');
            return 2;
        }
        if (!is_string($date) || $date === '') {
            $date = (new \DateTimeImmutable('now', new \DateTimeZone('UTC')))->format('Y-m-d');
        }
        $manifest = (new ReleaseNotesCollector($repo))->collect($version, $date);
        $output->writeln(json_encode(
            $manifest,
            JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR,
        ));
        return 0;
    })
    ->run();
