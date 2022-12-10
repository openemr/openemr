<?php

/**
 * CcdaNewpatientImport class.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2022 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Common\Command;

use Carecoordination\Model\CarecoordinationTable;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class CcdaNewpatientImport extends Command
{
    protected function configure(): void
    {
        $this
            ->setName('openemr:ccda-newpatient-import')
            ->setDescription('Import a new patient ccda directly from ccda')
            ->addUsage('--site=default --document=/file/path/file.xml')
            ->setDefinition(
                new InputDefinition([
                    new InputOption('document', null, InputOption::VALUE_REQUIRED, 'File (path) that will be imported to create the new patient'),
                    new InputOption('site', null, InputOption::VALUE_REQUIRED, 'Name of site', 'default'),
                ])
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        if (empty($input->getOption('document'))) {
            $output->writeln('document parameter is missing (required), so exiting');
            return 2;
        }
        if (!is_file($input->getOption('document'))) {
            $output->writeln('document does not exist, so exiting');
            return 1;
        }

        // get around a large ccda data array
        ini_set("memory_limit", -1);

        $GLOBALS['modules_application']->getServiceManager()->build(CarecoordinationTable::class)->importNewPatient($input->getOption('document'));
        return 0;
    }
}
