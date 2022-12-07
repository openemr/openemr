<?php

/**
 * CcdaImport class.
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

class CcdaImport extends Command
{
    protected function configure(): void
    {
        $this
            ->setName('openemr:ccda-import')
            ->setDescription('Import ccda into ccda table from a document id')
            ->setDefinition(
                new InputDefinition([
                    new InputOption('document_id', null, InputOption::VALUE_REQUIRED, 'Document id that will be imported into the ccda table'),
                    new InputOption('site', null, InputOption::VALUE_OPTIONAL, 'Name of site', 'default'),
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

        (new CarecoordinationTable())->import($input->getOption('document_id'));
        return 0;
    }
}
