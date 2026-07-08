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
use Installer\Model\InstModule;
use Installer\Model\InstModuleTable;
use OpenEMR\Core\OEGlobalsBag;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
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
            ->setDescription('Module maintenance (list, discover, install_sql, install_acl, upgrade_acl, upgrade_sql, install, enable, disable, unregister)')
            ->addUsage('--modaction=list')
            ->addUsage('--modaction=discover')
            ->addUsage('--site=default --modname=Carecoordination --modaction=install')
            ->setDefinition(
                new InputDefinition([
                    new InputOption('modname', null, InputOption::VALUE_REQUIRED, 'Name of module (mod_directory); not required for list or discover'),
                    new InputOption('modaction', null, InputOption::VALUE_REQUIRED, 'Available actions: list, discover, install_sql, install_acl, upgrade_acl, upgrade_sql, install, enable, disable, unregister'),
                    new InputOption('site', null, InputOption::VALUE_REQUIRED, 'Name of site', 'default'),
                ])
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        if (empty($input->getOption('modaction'))) {
            $output->writeln('modaction parameter is missing (required), so exiting');
            return 2;
        }

        $modaction = $input->getOption('modaction');
        $controller = new InstallerController(
            OEGlobalsBag::getInstance()->get('modules_application')->getServiceManager()->build(InstModuleTable::class)
        );

        if ($modaction === 'list') {
            $this->renderModuleList($controller->commandListModulesAction(), $output);
            return 0;
        }

        if ($modaction === 'discover') {
            $controller->scanAndRegisterCustomModules();
            $output->writeln('Scanned module directories; newly found modules are now registered (disabled). Run --modaction=list to review.');
            return 0;
        }

        if (empty($input->getOption('modname'))) {
            $output->writeln('modname parameter is missing (required), so exiting');
            return 2;
        }

        $modname = $input->getOption('modname');
        $controller->commandInstallModuleAction($modname, $modaction);
        return 0;
    }

    /**
     * @param InstModule[] $modules
     */
    private function renderModuleList(array $modules, OutputInterface $output): void
    {
        $table = new Table($output);
        $table->setHeaders(['Directory', 'Name', 'Type', 'Active', 'SQL ver', 'SQL pending', 'ACL ver', 'ACL pending']);
        foreach ($modules as $mod) {
            $table->addRow([
                $this->displayValue($mod->modDirectory),
                $this->displayValue($mod->modUiName),
                $this->isZend($mod->type) ? 'zend' : 'custom',
                $this->isActive($mod->modActive) ? 'yes' : 'no',
                $this->displayValue($mod->sql_version),
                $this->displayValue($mod->sql_action),
                $this->displayValue($mod->acl_version),
                $this->displayValue($mod->acl_action),
            ]);
        }
        $table->render();
    }

    /**
     * InstModule exposes untyped (mixed) properties sourced from the modules
     * table, so narrow at this boundary rather than blindly casting.
     */
    private function displayValue(mixed $value): string
    {
        return is_string($value) && $value !== '' ? $value : '-';
    }

    private function isZend(mixed $type): bool
    {
        return is_numeric($type) && (int) $type === InstModuleTable::MODULE_TYPE_ZEND;
    }

    private function isActive(mixed $active): bool
    {
        return is_numeric($active) && (int) $active === 1;
    }
}
