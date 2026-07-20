#!/usr/bin/env php
<?php

/**
 * Build a cumulative patch zip from the diff between a start tag and release branch.
 *
 * The patch is an overlay archive: users extract it on top of their existing
 * install. It can't delete files (but can empty them like setup.php) and always
 * includes version.php and sql_patch.php.
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
use Symfony\Component\Process\Process;

(new SingleCommandApplication())
    ->setName('patch-assemble')
    ->setDescription('Build cumulative patch zip from diff between tags')
    ->addOption('start-tag', null, InputOption::VALUE_REQUIRED, 'Base tag for diff (e.g., v8_0_0)')
    ->addOption('branch', null, InputOption::VALUE_REQUIRED, 'Release branch (e.g., rel-800)')
    ->addOption('filename', null, InputOption::VALUE_REQUIRED, 'Output zip filename')
    ->addOption('openemr-dir', null, InputOption::VALUE_REQUIRED, 'Path to openemr checkout')
    ->addOption('output-dir', null, InputOption::VALUE_REQUIRED, 'Output directory', './release-output')
    ->addOption('copy-styles', null, InputOption::VALUE_NONE, 'Include compiled theme styles')
    ->setCode(function (InputInterface $input, OutputInterface $output): int {
        /** @var string $startTag */
        $startTag = $input->getOption('start-tag');
        /** @var string $branch */
        $branch = $input->getOption('branch');
        /** @var string $filename */
        $filename = $input->getOption('filename');
        /** @var string $openemrDir */
        $openemrDir = $input->getOption('openemr-dir');
        /** @var string $outputDir */
        $outputDir = $input->getOption('output-dir');

        foreach (['start-tag', 'branch', 'filename', 'openemr-dir'] as $required) {
            if ($input->getOption($required) === null) {
                $output->writeln("<error>--{$required} is required</error>");
                return 1;
            }
        }

        if (!is_dir($openemrDir)) {
            $output->writeln("<error>OpenEMR directory not found: {$openemrDir}</error>");
            return 1;
        }

        $patchDir = "{$outputDir}/patch-staging";

        if (is_dir($patchDir)) {
            (new Process(['rm', '-rf', $patchDir]))->mustRun();
        }
        if (!is_dir($outputDir)) {
            mkdir($outputDir, 0755, true);
        }
        mkdir($patchDir, 0755, true);

        // Get changed files, excluding CI/dev/test paths
        // Exclude CI, testing, build tooling, and Docker configuration.
        // Keep in sync with build-patch.yml's exclude list.
        $excludes = [
            // Directories
            ':(exclude).github/**',
            ':(exclude).phpstan/**',
            ':(exclude).git/**',
            ':(exclude)ci/**',
            ':(exclude)docker/**',
            ':(exclude)tests/**',
            ':(exclude)tools/**',
            // Dotfiles
            ':!.codespell*',
            ':!.composer-require-checker.json',
            ':!.dclintrc.yaml',
            ':!.editorconfig',
            ':!.gitattributes',
            ':!.gitignore',
            ':!.gitmodules',
            ':!.hadolint.yaml',
            ':!.pre-commit-config.yaml',
            ':!.shellcheckrc',
            ':!.stylelintignore',
            ':!.stylelintrc.json',
            // Build/CI config files
            ':!build.xml',
            ':!cloudbuild.yaml',
            ':!codecov.yml',
            ':!composer.json',
            ':!composer.lock',
            ':!eslint.config.mjs',
            ':!gulpfile.js',
            ':!jest.config.js',
            ':!package.json',
            ':!package-lock.json',
            ':!phpcs.xml.dist',
            ':!phpstan.neon.dist',
            ':!phpunit*.xml',
            ':!rector*.php',
            ':!run-semgrep.sh',
            ':!semgrep.yaml',
            // Documentation
            ':!CLAUDE.md',
            ':!CONTRIBUTING.md',
            ':!CODE_OF_CONDUCT.md',
            ':!DOCKER_README.md',
            ':!README-Isolated-Testing.md',
        ];

        $diff = new Process(
            ['git', 'diff', '--name-only', '--diff-filter=d', "{$startTag}..{$branch}", '--', '.', ...$excludes],
            $openemrDir,
        );
        $diff->mustRun();

        $changedFiles = array_filter(
            explode("\n", trim($diff->getOutput())),
            static fn(string $line): bool => $line !== '',
        );
        file_put_contents("{$outputDir}/changed-files.txt", implode("\n", $changedFiles) . "\n");
        $output->writeln(sprintf('<info>%d</info> changed files', count($changedFiles)));

        // Copy each changed file preserving directory structure
        foreach ($changedFiles as $filepath) {
            $targetDir = "{$patchDir}/" . dirname($filepath);
            if (!is_dir($targetDir)) {
                mkdir($targetDir, 0755, true);
            }
            copy("{$openemrDir}/{$filepath}", "{$patchDir}/{$filepath}");
        }

        // Copy compiled theme styles if requested
        $copyStyles = (bool) $input->getOption('copy-styles');
        if ($copyStyles && is_dir("{$openemrDir}/public/themes")) {
            $themesDir = "{$patchDir}/public/themes";
            if (!is_dir($themesDir)) {
                mkdir($themesDir, 0755, true);
            }
            (new Process(['cp', '-R', "{$openemrDir}/public/themes/.", $themesDir]))->mustRun();
        }

        // Blank out setup.php so patch doesn't re-run setup
        file_put_contents("{$patchDir}/setup.php", '');

        // Always include version.php and sql_patch.php
        copy("{$openemrDir}/version.php", "{$patchDir}/version.php");
        copy("{$openemrDir}/sql_patch.php", "{$patchDir}/sql_patch.php");

        // Standardize permissions
        (new Process(['find', $patchDir, '-type', 'f', '-exec', 'chmod', '0644', '{}', '+']))->mustRun();

        // Count files in patch
        $findProcess = new Process(['find', $patchDir, '-type', 'f']);
        $findProcess->mustRun();
        $fileCount = count(array_filter(
            explode("\n", trim($findProcess->getOutput())),
            static fn(string $l): bool => $l !== '',
        ));
        $output->writeln(sprintf('<info>%d</info> files in patch', $fileCount));

        // Build zip
        (new Process(['zip', '-r', "../{$filename}", '.'], $patchDir))->mustRun();

        // Clean up staging directory
        (new Process(['rm', '-rf', $patchDir]))->mustRun();

        $output->writeln("Patch built: <info>{$outputDir}/{$filename}</info>");
        return 0;
    })
    ->run();
