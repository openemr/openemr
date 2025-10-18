<?php

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

abstract class AbstractTableAwareFixture extends AbstractFixture
{
    public function __construct(
        protected readonly string $recordsTablename,
    ) {
    }

    public function getRecordBy(string $columnName, string $columnValue): array
    {
        foreach ($this->records as $record) {
            if ($record[$columnName] === $columnValue) {
                return $record;
            }
        }

        throw new \Exception(sprintf(
            'Record with %s %s was not found at fixtures records',
            $columnName,
            $columnValue
        ));
    }

    protected function loadRecord(array $record): array
    {
        $record['id'] = QueryUtils::insertOne($this->recordsTablename, $record);

        return $record;
    }

    protected function removeRecord(array $record): void
    {
        QueryUtils::removeById($this->recordsTablename, $record['id']);
    }
}
