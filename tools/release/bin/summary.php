#!/usr/bin/env php
<?php

/**
 * Generate a release summary for GitHub step summary.
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
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

(new SingleCommandApplication())
    ->setName('summary')
    ->setDescription('Generate release summary for GitHub step summary')
    ->addOption('type', null, InputOption::VALUE_REQUIRED, 'Release type: "patch" or "full"')
    ->addOption('milestone', 'm', InputOption::VALUE_REQUIRED, 'Milestone name')
    ->addOption('output-dir', null, InputOption::VALUE_REQUIRED, 'Release artifacts directory', './release-output')
    ->addOption('output', 'o', InputOption::VALUE_REQUIRED, 'Output file (or GITHUB_STEP_SUMMARY)')
    ->addOption('release-tag', null, InputOption::VALUE_REQUIRED, 'Release tag')
    ->addOption('start-tag', null, InputOption::VALUE_REQUIRED, 'Start tag')
    ->addOption('version-branch', null, InputOption::VALUE_REQUIRED, 'Release branch')
    ->addOption('patch-filename', null, InputOption::VALUE_REQUIRED, 'Patch filename')
    ->addOption('patch-number', null, InputOption::VALUE_REQUIRED, 'Patch number')
    ->addOption('dry-run', null, InputOption::VALUE_NONE, 'Whether this is a dry run')
    ->addOption('copy-styles', null, InputOption::VALUE_NONE, 'Whether styles were copied')
    ->setCode(function (InputInterface $input, OutputInterface $output): int {
        // Parse raw CLI input into narrowed string values at the boundary
        // rather than @var-casting downstream; PHPStan trusts the runtime
        // check, and downstream code no longer needs re-validation.
        $required = [];
        foreach (['type', 'milestone'] as $name) {
            $value = $input->getOption($name);
            if (!is_string($value) || $value === '') {
                $output->writeln("<error>--{$name} is required</error>");
                return 1;
            }
            $required[$name] = $value;
        }
        $type = $required['type'];
        $milestone = $required['milestone'];

        $outputDirOption = $input->getOption('output-dir');
        if (!is_string($outputDirOption) || $outputDirOption === '') {
            $output->writeln('<error>--output-dir must be a non-empty string</error>');
            return 1;
        }
        $outputDir = $outputDirOption;

        if (!in_array($type, ['patch', 'full'], true)) {
            $output->writeln('<error>--type must be "patch" or "full"</error>');
            return 1;
        }

        $templateDir = dirname(__DIR__) . '/templates';
        $templateFile = "{$type}-summary.md.twig";
        if (!file_exists("{$templateDir}/{$templateFile}")) {
            $output->writeln("<error>Template not found: {$templateFile}</error>");
            return 1;
        }

        // Collect checksums
        $checksums = [];
        foreach (['md5', 'sha256', 'sha512'] as $ext) {
            $globResult = glob("{$outputDir}/*.{$ext}");
            if ($globResult === false) {
                continue;
            }
            foreach ($globResult as $file) {
                $checksums[] = trim((string) file_get_contents($file));
            }
        }

        // Read changelog
        $changelogFile = "{$outputDir}/changelog.md";
        $changelog = file_exists($changelogFile)
            ? trim((string) file_get_contents($changelogFile))
            : '';

        // Read changed files
        $changedFiles = [];
        $changedFilesPath = "{$outputDir}/changed-files.txt";
        if (file_exists($changedFilesPath)) {
            $raw = file($changedFilesPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            if ($raw !== false) {
                $changedFiles = $raw;
                sort($changedFiles);
            }
        }

        $twig = new Environment(
            new FilesystemLoader($templateDir),
            ['autoescape' => false],
        );

        $content = $twig->render($templateFile, [
            'milestone' => $milestone,
            'type' => $type,
            'checksums' => $checksums,
            'changelog' => $changelog,
            'changed_files' => $changedFiles,
            'release_tag' => $input->getOption('release-tag'),
            'start_tag' => $input->getOption('start-tag'),
            'version_branch' => $input->getOption('version-branch'),
            'patch_filename' => $input->getOption('patch-filename'),
            'patch_number' => $input->getOption('patch-number'),
            'dry_run' => (bool) $input->getOption('dry-run'),
            'copy_styles' => (bool) $input->getOption('copy-styles'),
        ]);

        // Determine output destination. --output is optional; validate the
        // shape when supplied rather than @var-casting downstream.
        $outputFileOption = $input->getOption('output');
        if ($outputFileOption !== null && !is_string($outputFileOption)) {
            $output->writeln('<error>--output must be a string</error>');
            return 1;
        }
        $envSummary = getenv('GITHUB_STEP_SUMMARY');
        $target = $outputFileOption ?? ($envSummary !== false ? $envSummary : null);

        if ($target !== null) {
            file_put_contents($target, $content, FILE_APPEND);
            $output->writeln("Summary written to <info>{$target}</info>");
        } else {
            $output->write($content);
        }

        return 0;
    })
    ->run();
