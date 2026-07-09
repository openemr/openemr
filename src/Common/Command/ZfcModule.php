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
        $modaction = $input->getOption('modaction');
        if (!is_string($modaction) || $modaction === '') {
            $output->writeln('modaction parameter is missing (required), so exiting');
            return 2;
        }

        $controller = new InstallerController(
            OEGlobalsBag::getInstance()->get('modules_application')->getServiceManager()->build(InstModuleTable::class)
        );

        if ($modaction === 'list') {
            $this->renderModuleList($controller->commandListModulesAction(), $output);
            $output->writeln('Only registered modules are shown. Run --modaction=discover to register newly added on-disk modules.');
            return 0;
        }

        if ($modaction === 'discover') {
            $registered = $controller->scanAndRegisterCustomModules();
            if ($registered === []) {
                $output->writeln('No new modules found; all on-disk modules are already registered.');
                return 0;
            }
            $output->writeln(sprintf('Registered %d new module(s), disabled by default:', count($registered)));
            foreach ($registered as $moduleDirectory) {
                $output->writeln('  - ' . $moduleDirectory);
            }
            $output->writeln('Run --modaction=list to review, then enable/install as needed.');
            return 0;
        }

        $modname = $input->getOption('modname');
        if (!is_string($modname) || $modname === '') {
            $output->writeln('modname parameter is missing (required), so exiting');
            return 2;
        }

        return $this->dispatchModuleAction($controller, $modname, $modaction, $output);
    }

    private function dispatchModuleAction(
        InstallerController $controller,
        string $modname,
        string $modaction,
        OutputInterface $output,
    ): int {
        $moduleId = $controller->getModuleId($modname);
        if ($moduleId === null) {
            $output->writeln(sprintf('Module "%s" is not registered; run --modaction=discover if it was added recently.', $modname));
            return 1;
        }

        $output->writeln(sprintf('Running "%s" on module "%s"...', $modaction, $modname));

        $result = match ($modaction) {
            'install_sql' => $controller->InstallModuleSQL($moduleId),
            'upgrade_sql' => $controller->UpgradeModuleSQL($moduleId),
            'install_acl' => $controller->InstallModuleACL($moduleId),
            'upgrade_acl' => $controller->UpgradeModuleACL($moduleId),
            'enable' => $controller->EnableModule($moduleId),
            'disable' => $controller->DisableModule($moduleId),
            'install' => $controller->InstallModule($moduleId),
            'unregister' => $controller->UnregisterModule($moduleId),
            default => throw new \InvalidArgumentException(sprintf('Unsupported action "%s".', $modaction)),
        };

        foreach ($this->resultLines($result) as $line) {
            $output->writeln($line);
        }
        $output->writeln('Done.');
        return 0;
    }

    /**
     * The installer methods return heterogeneous shapes (bool status, a status
     * message, or an array of log fragments). Normalise to plain-text lines for
     * console output.
     *
     * strip_tags() is applied because some methods (upgrade_sql, *_acl) were
     * written for the web UI and return HTML markup (spoiler divs, <br />), which
     * is noise in a terminal. The installer returning view markup is the real
     * smell; laundering it here is a pragmatic stopgap until those methods return
     * structured data.
     *
     * @return string[]
     */
    private function resultLines(mixed $result): array
    {
        if (is_array($result)) {
            $lines = [];
            foreach ($result as $line) {
                if (is_string($line)) {
                    $lines[] = trim(strip_tags($line));
                }
            }
            return $lines;
        }
        if (is_string($result) && $result !== '') {
            return [trim(strip_tags($result))];
        }
        return [];
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
