<?php

/**
 * CcdaNewpatient class.
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

class CcdaNewpatient extends Command
{
    protected function configure(): void
    {
        $this
            ->setName('openemr:ccda-newpatient')
            ->setDescription('Import new patient from audit table id and ccda document id')
            ->addUsage('--site=default --am_id=5 --document_id=7')
            ->setDefinition(
                new InputDefinition([
                    new InputOption('am_id', null, InputOption::VALUE_REQUIRED, 'The master audit table id of patient that will be imported as a new patient'),
                    new InputOption('document_id', null, InputOption::VALUE_REQUIRED, 'The ccda document id that was imported into the audit table'),
                    new InputOption('site', null, InputOption::VALUE_REQUIRED, 'Name of site', 'default'),
                ])
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        if (empty($input->getOption('document_id'))) {
            $output->writeln('document_id parameter is missing (required), so exiting');
            return 2;
        }
        if (empty($input->getOption('am_id'))) {
            $output->writeln('am_id parameter is missing (required), so exiting');
            return 2;
        }

        $GLOBALS['modules_application']->getServiceManager()->build(CarecoordinationTable::class)->insert_patient($input->getOption('am_id'), $input->getOption('document_id'));
        return 0;
    }
}
