<?php

/**
 * Register class.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2022 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Common\Command;

use Installer\Model\InstModuleTable;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class Register extends Command
{
    protected function configure(): void
    {
        $this
            ->setName('openemr:register')
            ->setDescription('Register a zend module')
            ->addUsage('--site=default --mtype=zend --modname=Multipledb')
            ->setDefinition(
                new InputDefinition([
                    new InputOption('mtype', null, InputOption::VALUE_REQUIRED, 'Only "zend" is supported'),
                    new InputOption('modname', null, InputOption::VALUE_REQUIRED, 'Zend module name'),
                    new InputOption('site', null, InputOption::VALUE_REQUIRED, 'Name of site', 'default'),
                ])
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        if (empty($input->getOption('mtype'))) {
            $output->writeln('mtype parameter is missing (required), so exiting');
            return 2;
        }
        if (empty($input->getOption('modname'))) {
            $output->writeln('modname parameter is missing (required), so exiting');
            return 2;
        }

        if ($input->getOption('mtype') != 'zend') {
            $output->writeln('mtype parameter that is not "zend" is not supported');
            return 1;
        }


        $moduleName = $input->getOption('modname');
        $rel_path = "public/" . $moduleName . "/";

        if ($GLOBALS['modules_application']->getServiceManager()->build(InstModuleTable::class)->register($moduleName, $rel_path, 0, $GLOBALS['zendModDir'])) {
            $output->writeln('Success');
            return 0;
        } else {
            $output->writeln('Failure');
            return 1;
        }
    }
}
