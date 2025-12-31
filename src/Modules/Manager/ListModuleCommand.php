<?php

declare(strict_types=1);

namespace OpenEMR\Modules\Manager;

use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    description: 'Lists all available modules',
    name: 'module:list',
)]
class ListModuleCommand extends Command
{
    public function __construct(
        private ModuleManager $manager,
    ) {
        parent::__construct();
    }

    // FC proxy to console 7.x
    public function execute(InputInterface $input, OutputInterface $output): int
    {
        return $this($output);
    }

    public function __invoke(OutputInterface $output): int
    {
        $installed = $this->manager->getInstalledModules();
        $table = new Table($output);
        $table->setHeaders(['Name', 'Active']);
        foreach ($installed as $module) {
            $table->addRow([
                $module->packageName,
                $module->isActive ? 'Yes' : 'No',
            ]);
        }

        $table->render();

        return Command::SUCCESS;
    }
}
