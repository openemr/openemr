<?php

/**
 * Reports a failed Composer check as GitHub annotations and a job summary
 *
 * The workflow runs the check and calls this only when it failed, so the
 * problem shows on the pull request instead of only in the log. The command
 * reports and succeeds; failing the step is left to the workflow.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Common\Command;

use OpenEMR\Common\ComposerAnnotations\ComposerCheck;
use OpenEMR\Common\ComposerAnnotations\Reporter;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;

#[AsCommand(
    name: 'openemr:composer-annotations',
    description: 'Report a failed Composer check as GitHub annotations and a job summary'
)]
final class ComposerAnnotationsCommand extends Command
{
    protected function configure(): void
    {
        $checks = array_map(static fn(ComposerCheck $check): string => $check->value, ComposerCheck::cases());
        $this
            ->addArgument('check', InputArgument::REQUIRED, 'The check that failed: ' . implode(', ', $checks), null, $checks)
            ->addArgument('output-file', InputArgument::REQUIRED, 'File holding what the check printed')
            ->addOption('annotate', null, InputOption::VALUE_NONE, 'Print an ::error workflow command for each finding')
            ->addOption('summary', null, InputOption::VALUE_REQUIRED, 'Append a Markdown summary to this file')
            ->setHelp(<<<'HELP'
                Reads what a failed Composer check printed, which the caller has already
                shown in the log, and reports its findings. With --annotate, each finding
                becomes an error annotation on composer.json, or on composer.lock for
                lock-file findings. With --summary, a Markdown report is appended to the
                given file.

                  require-checker  output of `composer require-checker -- --output=json`
                  validate         output of `composer validate --strict`, stdout and stderr
                  normalize        output of `composer normalize --dry-run`

                Run with --skip-globals; the command needs no database.

                Example:
                  <info>php bin/console --skip-globals %command.name% validate "${out}" --annotate --summary="${GITHUB_STEP_SUMMARY}"</info>
                HELP);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $check = ComposerCheck::tryFrom($this->stringArgument($input, 'check'));
        if ($check === null) {
            throw new \InvalidArgumentException('Unknown check. Use require-checker, validate, or normalize.');
        }
        $outputFile = $this->stringArgument($input, 'output-file');
        if (!is_file($outputFile) || !is_readable($outputFile)) {
            throw new \InvalidArgumentException(sprintf('Cannot read %s.', $outputFile));
        }
        $filesystem = new Filesystem();
        $checkOutput = $filesystem->readFile($outputFile);
        $report = (new Reporter())->report($check, $checkOutput);

        if ($checkOutput !== '' && !str_ends_with($checkOutput, chr(10))) {
            // The workflow has already printed the check's output. Start the workflow commands on a line
            // of their own, or the runner will not see them.
            $output->writeln('');
        }
        $output->write($report->log, false, OutputInterface::OUTPUT_RAW);
        if ($input->getOption('annotate') === true) {
            foreach ($report->annotations as $annotation) {
                $output->writeln($annotation->toWorkflowCommand(), OutputInterface::OUTPUT_RAW);
            }
        }
        $summary = $input->getOption('summary');
        if (is_string($summary) && $summary !== '') {
            $filesystem->appendToFile($summary, $report->summary);
        }
        return Command::SUCCESS;
    }

    private function stringArgument(InputInterface $input, string $name): string
    {
        $value = $input->getArgument($name);
        if (!is_string($value) || $value === '') {
            throw new \InvalidArgumentException($name . ' needs a value.');
        }
        return $value;
    }
}
