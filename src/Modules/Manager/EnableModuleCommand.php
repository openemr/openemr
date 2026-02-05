<?php

declare(strict_types=1);

namespace OpenEMR\Modules\Manager;

use Composer\InstalledVersions;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    description: 'Enbles a module',
    name: 'module:enable',
)]


class EnableModuleCommand extends Command
{
    public function __construct(
        private ManagerInterface $manager,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addArgument('package-name', InputArgument::REQUIRED);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $moduleName = $input->getArgument('package-name');
        return $this($moduleName, $output);
    }

    // 7.3+: remove configure+execute
    public function __invoke(string $moduleName, OutputInterface $output): int
    {
        $mi = $this->manager->getInfoFor($moduleName);
        if ($mi->isActive) {
            $output->writeln('Module is already active');
            return Command::SUCCESS; // clean exit so this is idempotent
        }
        $this->manager->enable($moduleName);
        $output->writeln(sprintf('%s activated', $moduleName));

        return Command::SUCCESS;
    }
}
