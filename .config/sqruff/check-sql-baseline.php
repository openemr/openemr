#!/usr/bin/env php
<?php

/**
 * Check SQL lint errors against baseline
 *
 * Compares current lint errors against the baseline file. Only reports errors
 * that exceed the baseline counts, allowing gradual improvement without
 * requiring all issues to be fixed at once.
 *
 * Note: This wrapper can be replaced if sqruff implements native baseline support.
 * See: https://github.com/quarylabs/sqruff/issues/2139
 *
 * @package   OpenEMR
 * @link      https://opencoreemr.com
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright 2026 OpenCoreEMR Inc.
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once __DIR__ . '/../../vendor/autoload.php';

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\SingleCommandApplication;
use Symfony\Component\Yaml\Yaml;

(new SingleCommandApplication())
    ->setName('check-sql-baseline')
    ->setDescription('Check SQL files against baseline (only fail on new errors)')
    ->addArgument('files', InputArgument::IS_ARRAY, 'SQL files to check (default: all .sql files)')
    ->addOption('baseline', 'b', InputOption::VALUE_REQUIRED, 'Baseline file path', '.config/sqruff/sql-baseline.yaml')
    ->addOption('format', 'f', InputOption::VALUE_REQUIRED, 'Output format (json|github)', 'json')
    ->setCode(function (InputInterface $input, OutputInterface $output): int {
        $files = $input->getArgument('files');
        $baselinePath = $input->getOption('baseline');
        $format = $input->getOption('format');
        $configPath = __DIR__ . '/.sqruff';

        // Load baseline
        $baseline = [];
        if (file_exists($baselinePath)) {
            $baseline = Yaml::parseFile($baselinePath);
        }

        $newErrors = [];
        $improvements = [];

        // If no files specified, scan all
        if (empty($files)) {
            $files = array_filter(explode("\0", trim(shell_exec("git ls-files -z '*.sql'") ?? '')));
        }

        foreach ($files as $file) {
            if (empty($file) || !file_exists($file)) {
                continue;
            }

            if (!str_ends_with($file, '.sql')) {
                continue;
            }

            $lintOutput = shell_exec("sqruff lint --config " . escapeshellarg($configPath) . " " . escapeshellarg($file) . " -f json 2>/dev/null");
            $results = json_decode($lintOutput ?: '{}', true);
            $errors = $results[$file] ?? [];

            // Count current errors by rule
            $currentCounts = [];
            foreach ($errors as $error) {
                $code = $error['code'];
                $currentCounts[$code] = ($currentCounts[$code] ?? 0) + 1;
            }

            $baselineCounts = $baseline[$file] ?? [];

            // Check for new errors (exceeding baseline)
            foreach ($currentCounts as $code => $count) {
                $allowed = $baselineCounts[$code] ?? 0;
                if ($count > $allowed) {
                    $excess = $count - $allowed;
                    $reported = 0;
                    foreach ($errors as $error) {
                        if ($error['code'] === $code && $reported < $excess) {
                            $newErrors[] = [
                                'file' => $file,
                                'line' => $error['range']['start']['line'] ?? 1,
                                'code' => $code,
                                'message' => $error['message'],
                                'baseline' => $allowed,
                                'current' => $count,
                            ];
                            $reported++;
                        }
                    }
                }
            }

            // Track improvements
            foreach ($baselineCounts as $code => $allowed) {
                $current = $currentCounts[$code] ?? 0;
                if ($current < $allowed) {
                    $improvements[] = [
                        'file' => $file,
                        'code' => $code,
                        'baseline' => $allowed,
                        'current' => $current,
                        'reduced' => $allowed - $current,
                    ];
                }
            }
        }

        if ($format === 'github') {
            foreach ($newErrors as $error) {
                $output->writeln(sprintf(
                    "::error file=%s,line=%d,title=sqruff::%s: %s (baseline: %d, current: %d)",
                    $error['file'],
                    $error['line'],
                    $error['code'],
                    $error['message'],
                    $error['baseline'],
                    $error['current']
                ));
            }
            foreach ($improvements as $imp) {
                $output->writeln(sprintf(
                    "::notice file=%s,title=baseline improvement::%s reduced from %d to %d (-%d)",
                    $imp['file'],
                    $imp['code'],
                    $imp['baseline'],
                    $imp['current'],
                    $imp['reduced']
                ));
            }
        } else {
            $result = [
                'new_errors' => $newErrors,
                'improvements' => $improvements,
                'summary' => [
                    'new_error_count' => count($newErrors),
                    'files_improved' => count(array_unique(array_column($improvements, 'file'))),
                    'total_reductions' => array_sum(array_column($improvements, 'reduced')),
                ],
            ];
            $output->writeln(json_encode($result, JSON_PRETTY_PRINT));
        }

        return count($newErrors) > 0 ? Command::FAILURE : Command::SUCCESS;
    })
    ->run();
