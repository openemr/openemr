<?php

/**
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Eric Stern <erics@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR <https://opencoreemr.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Console\Command\Codes;

use OpenEMR\Services\CodeTypes\CodeTypeMappingUpdater;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;

#[AsCommand(
    name: 'codes:update-mappings',
    description: 'Update list_options.codes mappings for activated code types (SNOMED, CPT4)',
)]
class UpdateMappingsCommand extends Command
{
    public function __construct(private readonly CodeTypeMappingUpdater $updater)
    {
        parent::__construct();
    }

    public function __invoke(): int
    {
        $this->updater->updateActivatedMappings();

        return Command::SUCCESS;
    }
}
