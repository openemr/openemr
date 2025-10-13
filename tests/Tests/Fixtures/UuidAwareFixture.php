<?php

declare(strict_types=1);

/**
 * @package   OpenEMR
 *
 * @link      http://www.open-emr.org
 *
 * @author    Igor Mukhin <igor.mukhin@gmail.com>
 * @copyright Copyright (c) 2025 OpenCoreEMR Inc
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Tests\Fixtures;

use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Common\Uuid\UuidRegistry;

abstract class UuidAwareFixture extends AbstractFixture
{
    protected function loadRecord(array $record): array
    {
        $record['uuid'] = (new UuidRegistry(['table_name' => $this->recordsTablename]))->createUuid();
        $record = parent::loadRecord($record);
        $record['uuid'] = UuidRegistry::uuidToString($record['uuid']);

        return $record;
    }

    protected function removeRecord(array $record): void
    {
        QueryUtils::removeBy('uuid_registry', [
            'table_name' => $this->recordsTablename,
            'uuid' => UuidRegistry::uuidToBytes($record['uuid']),
        ]);

        parent::removeRecord($record);
    }
}
