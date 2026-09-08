#!/usr/bin/env php
<?php

/**
 * Emit the `version=` / `version_branch=` / `release_tag=` lines the
 * build-release workflow appends to $GITHUB_OUTPUT, regardless of
 * whether the trigger was an `openemr-tag` repository_dispatch
 * (--payload-file) or a manual workflow_dispatch (--release-version /
 * --release-tag / --release-branch).
 *
 * Validation lives in TagDispatchPayload (mirrors the canonical
 * dispatch.schema.json patterns); a missing or malformed field aborts
 * the step instead of producing artifacts that reference "null".
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc.
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

require dirname(__DIR__, 3) . '/vendor/autoload.php';

use OpenEMR\Release\TagDispatchPayload;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\ConsoleOutputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\SingleCommandApplication;

(new SingleCommandApplication())
    ->setName('derive-build-inputs')
    ->setDescription('Emit version/version_branch/release_tag lines for the build-release workflow')
    ->addOption(
        'payload-file',
        null,
        InputOption::VALUE_REQUIRED,
        "Path to openemr-tag JSON envelope (use '-' for stdin). Mutually exclusive with --release-* flags.",
    )
    ->addOption('release-version', null, InputOption::VALUE_REQUIRED, 'Release version (e.g. 8.1.0)')
    ->addOption('release-tag', null, InputOption::VALUE_REQUIRED, 'Annotated release tag (e.g. v8_1_0)')
    ->addOption('release-branch', null, InputOption::VALUE_REQUIRED, 'Release branch (e.g. rel-810)')
    ->setCode(function (InputInterface $input, OutputInterface $output): int {
        // Stdout is reserved for the GITHUB_OUTPUT key=value lines the
        // workflow appends with `>>`. Errors must not pollute it.
        $err = $output instanceof ConsoleOutputInterface ? $output->getErrorOutput() : $output;
        $str = static function (string $name) use ($input): string {
            $value = $input->getOption($name);
            return is_string($value) ? $value : '';
        };
        $payloadFile = $str('payload-file');
        $version = $str('release-version');
        $tag = $str('release-tag');
        $branch = $str('release-branch');

        $flagsProvided = array_filter(
            [$version, $tag, $branch],
            static fn (string $v): bool => $v !== '',
        );
        if ($payloadFile !== '' && $flagsProvided !== []) {
            $err->writeln(
                '<error>--payload-file is mutually exclusive with --release-* flags</error>',
            );
            return 1;
        }
        if ($payloadFile === '' && count($flagsProvided) !== 3) {
            $err->writeln(
                '<error>Provide either --payload-file or all of'
                . ' --release-version/--release-tag/--release-branch</error>',
            );
            return 1;
        }

        try {
            $payload = $payloadFile !== ''
                ? TagDispatchPayload::fromPayloadFile($payloadFile)
                : new TagDispatchPayload($version, $tag, $branch);
        } catch (\JsonException $e) {
            $err->writeln(sprintf('<error>Payload is not valid JSON: %s</error>', $e->getMessage()));
            return 1;
        } catch (\RuntimeException $e) {
            $err->writeln(sprintf('<error>%s</error>', $e->getMessage()));
            return 1;
        }

        $output->writeln(sprintf('version=%s', $payload->version));
        $output->writeln(sprintf('version_branch=%s', $payload->branch));
        $output->writeln(sprintf('release_tag=%s', $payload->tag));
        return 0;
    })
    ->run();
