<?php

/**
 * Checks that file headers carry an allowed @license tag
 *
 * Pull requests and the pre-commit hook check only the files they change and
 * fail on any finding. The weekly scan checks every tracked file with
 * --report-only, so it reports compliance without failing.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Common\Command;

use OpenEMR\BC\ServiceContainer;
use OpenEMR\Common\LicenseHeaders\FileResult;
use OpenEMR\Common\LicenseHeaders\FileType;
use OpenEMR\Common\LicenseHeaders\GitFileLister;
use OpenEMR\Common\LicenseHeaders\HeaderChecker;
use OpenEMR\Common\LicenseHeaders\HeaderExtractor;
use OpenEMR\Common\LicenseHeaders\MarkdownReport;
use OpenEMR\Common\LicenseHeaders\Policy;
use OpenEMR\Common\LicenseHeaders\SarifReport;
use OpenEMR\Common\LicenseHeaders\ScanResult;
use Psr\Clock\ClockInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Filesystem\Path;

#[AsCommand(
    name: 'openemr:license-headers',
    description: 'Check that file headers carry an allowed @license tag'
)]
final class LicenseHeadersCommand extends Command
{
    public function __construct(
        private readonly ?ClockInterface $clock = null,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('paths', InputArgument::IS_ARRAY, 'Files to check, relative to the project root')
            ->addOption('all', null, InputOption::VALUE_NONE, 'Check every file git tracks')
            ->addOption('changed-since', null, InputOption::VALUE_REQUIRED, 'Check the files changed between the merge base of this ref and HEAD')
            ->addOption('report-only', null, InputOption::VALUE_NONE, 'Exit 0 even when files are not compliant')
            ->addOption('sarif', null, InputOption::VALUE_REQUIRED, 'Write findings as SARIF to this file')
            ->addOption('markdown', null, InputOption::VALUE_REQUIRED, 'Write a compliance summary as Markdown to this file')
            ->addOption('config', null, InputOption::VALUE_REQUIRED, 'Policy file, relative to the project root', '.license-headers.yaml')
            ->addOption('project-dir', null, InputOption::VALUE_REQUIRED, 'Project root', dirname(__DIR__, 3))
            ->setHelp(<<<'HELP'
                Checks the comment header of PHP, JS, CSS, SCSS, and Twig files. Each file
                needs a @license tag whose value the policy allows for its path. Paths the
                policy exempts, such as vendored and generated code, are skipped.

                Run with --skip-globals; the command needs no database.

                Examples:
                  <info>php bin/console --skip-globals %command.name% src/Foo.php</info>
                  <info>php bin/console --skip-globals %command.name% --changed-since=origin/master</info>
                  <info>php bin/console --skip-globals %command.name% --all --report-only --markdown=summary.md</info>
                HELP);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $projectDir = $this->stringOption($input, 'project-dir');
        $paths = $this->selectPaths($input, new GitFileLister($projectDir), $projectDir);
        $policy = Policy::fromFile(Path::makeAbsolute($this->stringOption($input, 'config'), $projectDir));
        $year = (int) ($this->clock ?? ServiceContainer::getClock())->now()->format('Y');
        $checker = new HeaderChecker($policy, new HeaderExtractor(), $year);
        $filesystem = new Filesystem();

        $results = [];
        foreach ($paths as $path) {
            $type = FileType::fromPath($path);
            $absolute = Path::join($projectDir, $path);
            if ($type === null || !is_file($absolute)) {
                continue;
            }
            $results[] = $checker->check($path, $type, $filesystem->readFile($absolute));
        }
        $scan = new ScanResult($results);

        foreach ($scan->findings() as $finding) {
            $output->writeln(
                sprintf('%s:%d:1: error - %s [%s]', $finding->path, $finding->line, $finding->message, $finding->rule->value),
                OutputInterface::OUTPUT_RAW,
            );
        }
        $sarif = $input->getOption('sarif');
        if (is_string($sarif) && $sarif !== '') {
            $filesystem->dumpFile($sarif, (new SarifReport())->render($scan));
        }
        $markdown = $input->getOption('markdown');
        if (is_string($markdown) && $markdown !== '') {
            $filesystem->dumpFile($markdown, (new MarkdownReport())->render($scan));
        }

        $failing = count(array_filter($scan->files, static fn(FileResult $file): bool => $file->findings !== []));
        $exempt = count(array_filter($scan->files, static fn(FileResult $file): bool => $file->exempt));
        $output->writeln(sprintf('%d files scanned: %d with findings, %d exempt.', count($scan->files), $failing, $exempt));

        if ($input->getOption('report-only') === true || !$scan->hasFindings()) {
            return Command::SUCCESS;
        }
        $output->writeln('See "File Headers" in AGENTS.md, and .license-headers.yaml for exemptions.');
        return Command::FAILURE;
    }

    /**
     * @return list<string> paths relative to the project root
     */
    private function selectPaths(InputInterface $input, GitFileLister $git, string $projectDir): array
    {
        $arguments = $input->getArgument('paths');
        $explicit = is_array($arguments) ? array_values(array_filter($arguments, is_string(...))) : [];
        $all = $input->getOption('all') === true;
        $since = $input->getOption('changed-since');
        $hasSince = is_string($since) && $since !== '';
        if (count(array_filter([$explicit !== [], $all, $hasSince])) !== 1) {
            throw new \InvalidArgumentException('Give exactly one of: file paths, --all, or --changed-since.');
        }
        if ($all) {
            return $git->tracked();
        }
        if ($hasSince) {
            return $git->changedSince($since);
        }
        return array_map(
            static fn(string $path): string => Path::isAbsolute($path) ? Path::makeRelative($path, $projectDir) : Path::canonicalize($path),
            $explicit,
        );
    }

    private function stringOption(InputInterface $input, string $name): string
    {
        $value = $input->getOption($name);
        if (!is_string($value) || $value === '') {
            throw new \InvalidArgumentException('--' . $name . ' needs a value.');
        }
        return $value;
    }
}
