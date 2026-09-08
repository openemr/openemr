#!/usr/bin/env php
<?php

/**
 * Fail if a consumer repo's vendored copies of cross-repo contracts have
 * drifted from the canonical sources here.
 *
 * Run by CI in consumer repos (openemr/openemr-devops, openemr/website-openemr)
 * to catch the case where the canonical contract is updated but a vendored
 * copy is not refreshed.
 *
 * @package   openemr
 * @link      https://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc.
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

require dirname(__DIR__, 3) . '/vendor/autoload.php';

use OpenEMR\Release\VendoredFileChecker;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\SingleCommandApplication;

(new SingleCommandApplication())
    ->setName('check-vendored')
    ->setDescription('Verify a consumer repo vendored copy matches the canonical contract')
    ->addOption(
        'canonical',
        null,
        InputOption::VALUE_REQUIRED,
        'Path to the canonical tools/release/ root',
        dirname(__DIR__),
    )
    ->addOption(
        'consumer',
        null,
        InputOption::VALUE_REQUIRED,
        'Path to the consumer repo dir holding the vendored copies',
    )
    ->addOption(
        'override',
        null,
        InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY,
        'Map a canonical path to a different consumer-relative path '
            . '(e.g. --override src/TagVerifier.php=src/Release/TagVerifier.php). Repeatable.',
    )
    ->addOption(
        'overrides',
        null,
        InputOption::VALUE_REQUIRED,
        'Same mapping as --override but as a single newline-delimited string '
            . '(blank lines and surrounding whitespace ignored). Convenient for '
            . 'CI inputs that arrive as multi-line strings. Combines with --override.',
    )
    ->setCode(function (InputInterface $input, OutputInterface $output): int {
        $canonical = $input->getOption('canonical');
        if (!is_string($canonical) || $canonical === '') {
            $output->writeln('<error>--canonical is required</error>');
            return 1;
        }
        if (!is_dir($canonical)) {
            $output->writeln(sprintf('<error>Canonical dir not found: %s</error>', $canonical));
            return 1;
        }

        $consumer = $input->getOption('consumer');
        if (!is_string($consumer) || $consumer === '') {
            $output->writeln('<error>--consumer is required</error>');
            return 1;
        }
        if (!is_dir($consumer)) {
            $output->writeln(sprintf('<error>Consumer dir not found: %s</error>', $consumer));
            return 1;
        }

        $rawOverrides = $input->getOption('override');
        if (!is_array($rawOverrides)) {
            $rawOverrides = [];
        }
        $multiline = $input->getOption('overrides');
        if (is_string($multiline) && $multiline !== '') {
            $lines = preg_split('/\R/', $multiline);
            if ($lines === false) {
                $output->writeln('<error>--overrides could not be parsed</error>');
                return 1;
            }
            foreach ($lines as $line) {
                $trimmed = trim($line);
                if ($trimmed !== '') {
                    $rawOverrides[] = $trimmed;
                }
            }
        }
        $overrides = [];
        foreach ($rawOverrides as $entry) {
            if (!is_string($entry) || !str_contains($entry, '=')) {
                $output->writeln(sprintf(
                    '<error>override entry expects CANONICAL=CONSUMER, got: %s</error>',
                    is_string($entry) ? $entry : gettype($entry),
                ));
                return 1;
            }
            [$canonicalPath, $consumerPath] = explode('=', $entry, 2);
            if ($canonicalPath === '' || $consumerPath === '') {
                $output->writeln(sprintf('<error>override entry has empty side: %s</error>', $entry));
                return 1;
            }
            $overrides[$canonicalPath] = $consumerPath;
        }

        try {
            $checker = new VendoredFileChecker($canonical, $consumer, $overrides);
        } catch (\InvalidArgumentException $e) {
            $output->writeln(sprintf('<error>%s</error>', $e->getMessage()));
            return 1;
        }
        $issues = $checker->check();
        if ($issues === []) {
            $output->writeln(sprintf(
                '<info>✓</info> All %d vendored file(s) match canonical.',
                count(VendoredFileChecker::VENDORED_PATHS),
            ));
            return 0;
        }

        $output->writeln(sprintf('<error>✗</error> Vendored drift detected (%d issue(s)):', count($issues)));
        foreach ($issues as $issue) {
            $output->writeln(sprintf('  %s  [%s]  %s', $issue->relativePath, $issue->kind, $issue->message));
        }
        $output->writeln('');
        $output->writeln('Re-vendor each drifted file from the canonical openemr/openemr checkout.');
        return 1;
    })
    ->run();
