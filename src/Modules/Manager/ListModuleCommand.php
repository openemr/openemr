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
    description: 'Lists all available modules',
    name: 'module:list',
)]
class ListModuleCommand extends Command
{
    public function __construct(
        private ModuleFinder $finder,
    ) {
        parent::__construct();
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        return $this($output);
    }

    public function __invoke(OutputInterface $output): int
    {
        $available = $this->finder->listAllAvailable();

        $io = new \Composer\IO\NullIO();
        $c = \Composer\Factory::create($io);
        $repo = $c->getRepositoryManager()->getLocalRepository();
        // var_dump($available);
        // print into a table?
        $table = new Table($output);
        $table->setHeaders(['Name', 'Active']);
        foreach ($available as $module) {
            // $info = InstalledVersions::GetInstallPath($module);
            // var_dump($module, $info);
            // $package = $repo->findPackage($module, '*');
            // print_r(get_class($package));
            // var_dump($package instanceof \Composer\Package\AliasPackage);
            // $extra = $package->getExtra();
            // print_r($extra);
            $mi = ModuleInfo::for($module);
            // print_r($mi);
            $table->addRow([$mi->packageName, $mi->isActive ? 'Yes' : 'No']);
        }

        $table->render();

        return Command::SUCCESS;
    }
}
