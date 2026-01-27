#!/usr/bin/env php
<?php

/**
 * Generate SQL lint baseline file
 *
 * Scans SQL files and PHP files with embedded SQL, counts errors per file per rule,
 * and outputs a YAML baseline file that can be used to track progress.
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
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\SingleCommandApplication;
use Symfony\Component\Yaml\Yaml;

(new SingleCommandApplication())
    ->setName('generate-sql-baseline')
    ->setDescription('Generate SQL lint baseline file from current codebase state')
    ->addOption('output', 'o', InputOption::VALUE_REQUIRED, 'Output file path', '.config/sqruff/sql-baseline.yaml')
    ->addOption('sql-only', null, InputOption::VALUE_NONE, 'Only scan .sql files (skip PHP)')
    ->addOption('php-only', null, InputOption::VALUE_NONE, 'Only scan PHP files (skip .sql)')
    ->setCode(function (InputInterface $input, OutputInterface $output): int {
        $outputPath = $input->getOption('output');
        $sqlOnly = $input->getOption('sql-only');
        $phpOnly = $input->getOption('php-only');
        $configPath = __DIR__ . '/.sqruff';
        $extractorPath = __DIR__ . '/extract-sql-from-php.php';

        $baseline = [];

        // Lint .sql files
        if (!$phpOnly) {
            $output->writeln('<info>Scanning .sql files...</info>');
            $sqlFiles = explode("\0", trim(shell_exec("git ls-files -z '*.sql'") ?? ''));
            $sqlFiles = array_filter($sqlFiles);

            if (!empty($sqlFiles)) {
                $cmd = 'sqruff lint --config ' . escapeshellarg($configPath) . ' -f json ' . implode(' ', array_map('escapeshellarg', $sqlFiles)) . ' 2>/dev/null';
                $lintOutput = shell_exec($cmd);

                if (!empty($lintOutput)) {
                    $results = json_decode($lintOutput, true);
                    if (is_array($results)) {
                        foreach ($results as $file => $errors) {
                            if (empty($errors)) {
                                continue;
                            }
                            $counts = [];
                            foreach ($errors as $error) {
                                $code = $error['code'];
                                $counts[$code] = ($counts[$code] ?? 0) + 1;
                            }
                            if (!empty($counts)) {
                                ksort($counts);
                                $baseline[$file] = $counts;
                            }
                        }
                    }
                }
            }
        }

        // Lint SQL in PHP files
        if (!$sqlOnly) {
            $output->writeln('<info>Scanning PHP files for embedded SQL...</info>');
            $phpFiles = explode("\0", trim(shell_exec("git ls-files -z '*.php' '*.inc'") ?? ''));
            $phpFiles = array_filter($phpFiles);

            foreach ($phpFiles as $file) {
                if (empty($file)) {
                    continue;
                }

                $extractOutput = shell_exec("php " . escapeshellarg($extractorPath) . " " . escapeshellarg($file) . " --json 2>/dev/null");
                if (empty($extractOutput)) {
                    continue;
                }

                $statements = json_decode($extractOutput, true);
                if (empty($statements)) {
                    continue;
                }

                $counts = [];
                foreach ($statements as $stmt) {
                    $sql = $stmt['sql'];
                    if (!preg_match('/;\s*$/', $sql)) {
                        $sql .= ';';
                    }

                    $descriptors = [
                        0 => ['pipe', 'r'],
                        1 => ['pipe', 'w'],
                        2 => ['pipe', 'w'],
                    ];

                    $process = proc_open(
                        'sqruff lint --config ' . escapeshellarg($configPath) . ' - --dialect mysql -f json',
                        $descriptors,
                        $pipes
                    );

                    if (!is_resource($process)) {
                        continue;
                    }

                    fwrite($pipes[0], $sql);
                    fclose($pipes[0]);

                    $lintOutput = stream_get_contents($pipes[1]);
                    fclose($pipes[1]);
                    fclose($pipes[2]);
                    proc_close($process);

                    $lintResults = json_decode($lintOutput, true);
                    if (empty($lintResults) || empty($lintResults['<string>'])) {
                        continue;
                    }

                    foreach ($lintResults['<string>'] as $error) {
                        $code = $error['code'];
                        $counts[$code] = ($counts[$code] ?? 0) + 1;
                    }
                }

                if (!empty($counts)) {
                    ksort($counts);
                    $baseline[$file] = $counts;
                }
            }
        }

        ksort($baseline);

        $yaml = Yaml::dump($baseline, 2, 2);
        file_put_contents($outputPath, $yaml);

        $totalFiles = count($baseline);
        $totalErrors = 0;
        foreach ($baseline as $counts) {
            $totalErrors += array_sum($counts);
        }

        $output->writeln("<info>Baseline generated: $outputPath</info>");
        $output->writeln("<info>Files with issues: $totalFiles</info>");
        $output->writeln("<info>Total errors: $totalErrors</info>");

        return Command::SUCCESS;
    })
    ->run();
