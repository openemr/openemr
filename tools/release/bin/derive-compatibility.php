#!/usr/bin/env php
<?php

/**
 * Inject a "Minimum supported versions" section into a release-notes file,
 * derived from a checked-out openemr/openemr release branch's CI test matrix.
 *
 * Thin CLI wrapper around OpenEMR\Release\CompatibilityDeriver (decode rules,
 * mirroring ci/parse_docker_dir.sh) and CompatibilityNotesRenderer (markdown).
 * The section is inserted just after the first `## ` version heading in the
 * notes file, or prepended if the file has no such heading.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc.
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

require dirname(__DIR__, 3) . '/vendor/autoload.php';

// OpenEMR\Release\ classes live under autoload-dev so composer-require-checker
// does not demand conductor-only deps in production. Anything invoking this
// script needs a `composer install` that includes dev dependencies.
if (!class_exists(\OpenEMR\Release\CompatibilityDeriver::class)) {
    fwrite(
        STDERR,
        "OpenEMR\\Release\\ classes are not autoloadable; rerun composer install with dev dependencies.\n",
    );
    exit(2);
}

use OpenEMR\Release\CompatibilityDeriver;
use OpenEMR\Release\CompatibilityNotesRenderer;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\SingleCommandApplication;

(new SingleCommandApplication())
    ->setName('derive-compatibility')
    ->setDescription('Inject the minimum-supported-versions section into the release notes')
    ->addOption(
        'openemr-dir',
        null,
        InputOption::VALUE_REQUIRED,
        'Path to the checked-out openemr release branch (defaults to $OPENEMR_DIR)',
    )
    ->addOption(
        'version-branch',
        null,
        InputOption::VALUE_REQUIRED,
        'Release branch for the CI matrix link (e.g., rel-810)',
    )
    ->addOption('repo', null, InputOption::VALUE_REQUIRED, 'GitHub repo hosting the CI matrix', 'openemr/openemr')
    ->addOption('notes-file', null, InputOption::VALUE_REQUIRED, 'Release-notes markdown file to inject into')
    ->setCode(function (InputInterface $input, OutputInterface $output): int {
        $openemrDirOption = $input->getOption('openemr-dir');
        $envOpenemrDir = getenv('OPENEMR_DIR');
        $openemrDir = is_string($openemrDirOption) && $openemrDirOption !== ''
            ? $openemrDirOption
            : (is_string($envOpenemrDir) ? $envOpenemrDir : '');
        if ($openemrDir === '') {
            $output->writeln('<error>--openemr-dir is required (or set $OPENEMR_DIR)</error>');
            return 1;
        }

        $versionBranchOption = $input->getOption('version-branch');
        if (!is_string($versionBranchOption) || $versionBranchOption === '') {
            $output->writeln('<error>--version-branch is required</error>');
            return 1;
        }

        $notesFileOption = $input->getOption('notes-file');
        if (!is_string($notesFileOption) || $notesFileOption === '') {
            $output->writeln('<error>--notes-file is required</error>');
            return 1;
        }
        if (!is_file($notesFileOption)) {
            $output->writeln("<error>Notes file not found: {$notesFileOption}</error>");
            return 1;
        }

        $repoOption = $input->getOption('repo');
        $repo = is_string($repoOption) && $repoOption !== '' ? $repoOption : 'openemr/openemr';

        $testedMatrixUrl = sprintf('https://github.com/%s/tree/%s/ci', $repo, $versionBranchOption);

        $ciDir = rtrim($openemrDir, '/') . '/ci';

        $notes = file_get_contents($notesFileOption);
        if ($notes === false) {
            $output->writeln("<error>Could not read notes file: {$notesFileOption}</error>");
            return 1;
        }

        try {
            $minimums = (new CompatibilityDeriver($ciDir))->derive();
            $renderer = new CompatibilityNotesRenderer();
            $merged = $renderer->inject($notes, $renderer->render($minimums, $testedMatrixUrl));
        } catch (\RuntimeException $e) {
            $output->writeln(sprintf('<error>%s</error>', $e->getMessage()));
            return 1;
        }

        if (file_put_contents($notesFileOption, $merged) === false) {
            $output->writeln("<error>Could not write notes file: {$notesFileOption}</error>");
            return 1;
        }

        $output->writeln("<info>Injected compatibility section into</info> {$notesFileOption}");
        return 0;
    })
    ->run();
