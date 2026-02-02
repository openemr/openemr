#!/usr/bin/env php
<?php

/**
 * Lint SQL embedded in PHP files using sqruff
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

(new SingleCommandApplication())
    ->setName('lint-php-sql')
    ->setDescription('Lint SQL embedded in PHP files using sqruff')
    ->addArgument('files', InputArgument::IS_ARRAY | InputArgument::REQUIRED, 'PHP files to lint')
    ->addOption('format', 'f', InputOption::VALUE_REQUIRED, 'Output format (json|github)', 'json')
    ->setCode(function (InputInterface $input, OutputInterface $output): int {
        $files = $input->getArgument('files');
        $format = $input->getOption('format');
        $extractorPath = __DIR__ . '/extract-sql-from-php.php';
        $configPath = __DIR__ . '/.sqruff';

        $allResults = [];

        foreach ($files as $file) {
            if (!file_exists($file)) {
                $output->writeln("<error>File not found: $file</error>", OutputInterface::VERBOSITY_VERBOSE);
                continue;
            }

            // Extract SQL from PHP file
            $extractOutput = shell_exec("php " . escapeshellarg($extractorPath) . " " . escapeshellarg($file) . " --json 2>/dev/null");
            if (empty($extractOutput)) {
                continue;
            }

            $statements = json_decode($extractOutput, true);
            if (empty($statements)) {
                continue;
            }

            foreach ($statements as $stmt) {
                $sql = $stmt['sql'];
                if (!preg_match('/;\s*$/', $sql)) {
                    $sql .= ';';
                }

                // Lint this SQL statement
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
                    $allResults[] = [
                        'file' => $stmt['file'],
                        'line' => $stmt['line'],
                        'sql_line' => $error['range']['start']['line'],
                        'code' => $error['code'],
                        'message' => $error['message'],
                        'sql' => $sql,
                    ];
                }
            }
        }

        if ($format === 'github') {
            foreach ($allResults as $error) {
                $output->writeln(sprintf(
                    "::error file=%s,line=%d,title=sqruff::%s: %s",
                    $error['file'],
                    $error['line'],
                    $error['code'],
                    $error['message']
                ));
            }
        } else {
            $output->writeln(json_encode($allResults, JSON_PRETTY_PRINT));
        }

        return count($allResults) > 0 ? Command::FAILURE : Command::SUCCESS;
    })
    ->run();
