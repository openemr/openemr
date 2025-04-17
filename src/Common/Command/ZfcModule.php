<?php

/**
 * ZfcModule class.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2022 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Common\Command;

use Installer\Controller\InstallerController;
use Installer\Model\InstModuleTable;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ZfcModule extends Command
{
    protected function configure(): void
    {
        $this
            ->setName('openemr:zfc-module')
            ->setDescription('Module maintenance (install_sql, install_acl, upgrade_acl, upgrade_sql, install, enable, disable, unregister)')
            ->addUsage('--site=default --modname=Multipledb --modaction=install')
            ->setDefinition(
                new InputDefinition([
                    new InputOption('modname', null, InputOption::VALUE_REQUIRED, 'Name of module'),
                    new InputOption('modaction', null, InputOption::VALUE_REQUIRED, 'Available actions: install_sql, install_acl, upgrade_acl, upgrade_sql, install, enable, disable, unregister'),
                    new InputOption('site', null, InputOption::VALUE_REQUIRED, 'Name of site', 'default'),
                ])
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        if (empty($input->getOption('modname'))) {
            $output->writeln('modname parameter is missing (required), so exiting');
            return 2;
        }
        if (empty($input->getOption('modaction'))) {
            $output->writeln('modaction parameter is missing (required), so exiting');
            return 2;
        }

        $modname = $input->getOption('modname');
        $modaction = $input->getOption('modaction');
        (new InstallerController($GLOBALS['modules_application']->getServiceManager()->build(InstModuleTable::class)))->commandInstallModuleAction($modname, $modaction);
        return 0;
    }
}
