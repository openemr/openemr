#!/usr/bin/env php
<?php

/**
 * Merge the three release PRs (conductor → docs → finalize) in order.
 *
 * Authenticates via the ambient GH_TOKEN env var. The workflow mints a release
 * App token with PR-write on both repos and exports it before invoking.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc.
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

require dirname(__DIR__, 3) . '/vendor/autoload.php';

use OpenEMR\Release\GhPullRequestApi;
use OpenEMR\Release\Mode;
use OpenEMR\Release\PullRequestTarget;
use OpenEMR\Release\ShipReleaseOptions;
use OpenEMR\Release\ShipReleaseOrchestrator;
use OpenEMR\Release\ShipReleaseRenderer;
use OpenEMR\Release\ShipReleaseSummaryRenderer;
use OpenEMR\Release\SystemClock;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\SingleCommandApplication;
use Symfony\Component\Filesystem\Filesystem;

(new SingleCommandApplication())
    ->setName('ship-release')
    ->setDescription('Merge the three release PRs in order (issue #705)')
    // Option is `--release-version`, not `--version`: Symfony Console reserves
    // `--version`/`-V` as a global flag that prints the app name and exits 0
    // before the command runs, so `--version=8.1.0` would silently no-op.
    ->addOption('release-version', null, InputOption::VALUE_REQUIRED, 'Release version (e.g. 8.1.0)')
    ->addOption('rel-branch', null, InputOption::VALUE_REQUIRED, 'Release branch name (e.g. rel-810)')
    ->addOption('dry-run', null, InputOption::VALUE_NONE, 'Check readiness without merging or posting status')
    ->addOption(
        'mode',
        null,
        InputOption::VALUE_REQUIRED,
        'Execution mode: dry-run | semi-auto | full-auto (see OpenEMR\\Release\\Mode)',
        Mode::SemiAuto->value,
    )
    ->addOption(
        'timeout-seconds',
        null,
        InputOption::VALUE_REQUIRED,
        'Max seconds to wait for downstream PRs to update after conductor merges',
        '600',
    )
    ->addOption('status-target-url', null, InputOption::VALUE_REQUIRED, 'target_url for the ship-approved status', '')
    ->addOption('summary-file', null, InputOption::VALUE_REQUIRED, 'Write a Markdown run summary to this path', '')
    ->setCode(function (InputInterface $input, OutputInterface $output): int {
        $version = ShipReleaseOptions::asString($input, 'release-version');
        $relBranch = ShipReleaseOptions::asString($input, 'rel-branch');
        if ($version === '' || $relBranch === '') {
            $output->writeln('<error>--release-version and --rel-branch are required</error>');
            return 1;
        }
        $timeoutRaw = ShipReleaseOptions::asString($input, 'timeout-seconds');
        if (!ctype_digit($timeoutRaw) || (int) $timeoutRaw < 1) {
            $output->writeln('<error>--timeout-seconds must be a positive integer</error>');
            return 1;
        }
        $modeString = ShipReleaseOptions::asString($input, 'mode');
        $mode = Mode::tryFrom($modeString);
        if ($mode === null) {
            $output->writeln(sprintf(
                '<error>--mode must be one of: dry-run, semi-auto, full-auto (got %s)</error>',
                $modeString,
            ));
            return 1;
        }
        // --dry-run always wins for safety when both flags are provided.
        if ((bool) $input->getOption('dry-run')) {
            $mode = Mode::DryRun;
        }

        $orchestrator = new ShipReleaseOrchestrator(
            new GhPullRequestApi(),
            new SystemClock(),
            $version,
            (int) $timeoutRaw,
            $mode,
            ShipReleaseOptions::asString($input, 'status-target-url'),
        );
        $result = $orchestrator->ship(PullRequestTarget::forRelease($version, $relBranch));
        ShipReleaseRenderer::render($output, $result);

        $summaryFile = ShipReleaseOptions::asString($input, 'summary-file');
        if ($summaryFile !== '') {
            $markdown = ShipReleaseSummaryRenderer::render(
                $version,
                $relBranch,
                $mode === Mode::DryRun,
                $result,
            );
            (new Filesystem())->appendToFile($summaryFile, $markdown);
        }

        return $result->wasSuccessful() ? 0 : 1;
    })
    ->run();
