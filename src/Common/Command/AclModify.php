<?php

/**
 * AclModify class.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2022 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Common\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class AclModify extends Command
{
    protected function configure(): void
    {
        $this
            ->setName('openemr:acl-modify')
            ->setDescription('Modify ACL group of module (enable, disable)')
            ->addUsage('--site=default --modname=Multipledb --aclgroup=admin --aclaction=enable')
            ->setDefinition(
                new InputDefinition([
                    new InputOption('modname', null, InputOption::VALUE_REQUIRED, 'Name of module'),
                    new InputOption('aclgroup', null, InputOption::VALUE_REQUIRED, 'Name of ACL group'),
                    new InputOption('aclaction', null, InputOption::VALUE_REQUIRED, 'Available actions: enable, disable'),
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
        if (empty($input->getOption('aclgroup'))) {
            $output->writeln('aclgroup parameter is missing (required), so exiting');
            return 2;
        }
        if (empty($input->getOption('aclaction'))) {
            $output->writeln('aclaction parameter is missing (required), so exiting');
            return 2;
        }

        $moduleName = $input->getOption('modname');
        $groupAclName = $input->getOption('aclgroup');
        $action = $input->getOption('aclaction');

        $output->writeln('--- Run command [' . $action . '] [' . $groupAclName . '] in module:  ' . $moduleName . '---' . PHP_EOL);
        $output->writeln('start process - ' . date('Y-m-d H:i:s') . PHP_EOL);

        // Set allowed
        $allowed = 0;
        if ($action == 'enable') {
            $allowed = 1;
        }

        // Get module id
        $moduleId = sqlQuery("SELECT `mod_id` FROM `modules` WHERE `mod_name` = ?", [$moduleName])['mod_id'];

        // Get section ids
        $res = sqlStatement("SELECT `section_id` FROM `module_acl_sections` WHERE `module_id` = ?", [$moduleId]);
        $ids = [];
        while ($row = sqlFetchArray($res)) {
            $ids[] = $row['section_id'];
        }

        // Get group acl id
        $groupAclId = sqlQuery("SELECT `id` FROM `gacl_aro_groups` WHERE `value` = ?", [$groupAclName])['id'];

        // Modify the acl group settings
        foreach ($ids as $id) {
            sqlStatement(
                "REPLACE INTO `module_acl_group_settings` SET `module_id` = ?, `group_id` = ?, `section_id` = ?, `allowed` = ?",
                [
                    $moduleId,
                    $groupAclId,
                    $id,
                    $allowed
                ]
            );
        }

        $output->writeln('command completed successfully - ' . date('Y-m-d H:i:s') . PHP_EOL);

        return 0;
    }
}
