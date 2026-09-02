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

namespace OpenEMR\Console\Command\Codes;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use OpenEMR\Console\Command\Traits\CommandLineFSTrait;

#[AsCommand(
    name: 'codes:import',
    description: 'Given a directory path, walk through it and import available codes (ValueSet, SNOMED, etc...)',
)]
class ImportCommand extends Command
{
    use CommandLineFSTrait;
    public function __construct(private readonly string $path = '/var/www/localhost/htdocs/openemr/contrib')
    {
        parent::__construct();
    }

    public function __invoke(): int
    {
        if (strlen($this->path)) {
            $this->import(realpath($this->path));

            return Command::SUCCESS;
        } else {
            return Command::FAILURE;
        }
    }
}
