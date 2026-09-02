<?php

/**
 * =======================================
 * OpenEMR Automated Code Import
 * =======================================
 * This class walks through a directory tree up to one level to find archives of Code System databases.
 *
 * The import limit here is dictated by available functionality in OpenEMR. Currently, expect to import RXNORM,
 * SNOMED, ICD9, ICD10 CM, ICD10 PCS, CQM_VALUESET, and any arbitrary dataset that conforms to the VALUESET format.
 *
 * By default, we look into `/var/www/localhost/htdocs/openemr/contrib`, which is the default path used by the frontend importer.
 *
 * Usage:
 *   php auto_import_codes.php [dir_root]
 *
 * Example:
 *   php auto_import_codes.php
 *   php auto_import_codes.php codes
 * ============================================================================
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Luis M. Santos, MD <lsantos@medicalmasses.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Common\Command;

use OpenEMR\Core\OEGlobalsBag;
use OpenEMR\Services\CodeTypes\CodeTypeImporter;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class CodeImportCommand extends Command
{
    protected function configure(): void
    {
        $this
            ->setName('openemr:codes:import')
            ->setDescription('Import codes such as SNOMED and ICD10 from the console.')
            ->addUsage('--dir /path/to/directory')
            ->setDefinition(
                new InputDefinition([
                    new InputOption('dir', 'd', InputOption::VALUE_REQUIRED, 'Directory to navigate and import codes from.', '/var/www/localhost/htdocs/openemr/contrib'),
                ])
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        if (empty($input->getOption('dir'))) {
            $output->writeln('directory parameter is missing (required), so exiting');
            return 1;
        }

        $importer = OEGlobalsBag::getInstance()->get('modules_application')->getServiceManager()->build(CodeTypeImporter::class);
        $path = $input->getOption('dir');
        if (strlen($path)) {
            $importer->import(realpath($path));
            return Command::SUCCESS;
        }
        return Command::FAILURE;
    }
}
