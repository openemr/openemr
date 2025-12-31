<?php

declare(strict_types=1);

namespace OpenEMR\Modules\Manager;

use Composer\InstalledVersions;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    description: 'Enbles a module',
    name: 'module:enable',
)]


class EnableModuleCommand extends Command
{
    public function __construct(
        private ModuleFinder $finder,
    ) {
        parent::__construct();
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $moduleName = 'firehed/openemr-sample-module'; // FIXME: read from $input
        return $this($moduleName, $output);
    }

    public function __invoke(string $moduleName, OutputInterface $output): int
    {
        // $available = $this->finder->listAllAvailable();
        $mi = ModuleInfo::for($moduleName);
        if ($mi->isActive) {
            $output->writeln('Module is already active');
            return Command::SUCCESS; // clean exit so this is idempotent
        }

        $mm = new ModuleManager();
        $mm->enable($moduleName);
        $output->writeln(sprintf('%s activated', $moduleName));

        return Command::SUCCESS;
    }
}
