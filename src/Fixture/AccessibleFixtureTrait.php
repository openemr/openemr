<?php

/**
 * @package   OpenEMR
 *
 * @link      http://www.open-emr.org
 * @link      https://opencoreemr.com
 *
 * @author    Igor Mukhin <igor.mukhin@gmail.com>
 * @copyright Copyright (c) 2025 OpenCoreEMR Inc
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Fixture;

/**
 * @template TRecord of array
 *
 * @mixin AbstractFixture
 */
trait AccessibleFixtureTrait
{
    /**
     * @phpstan-return array<TRecord>
     */
    public function getRecords(): array
    {
        return $this->records;
    }

    /**
     * @phpstan-return TRecord
     */
    public function getRandomRecord(): array
    {
        $index = array_rand($this->records, 1);

        return $this->records[$index];
    }

    /**
     * @phpstan-return TRecord
     */
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
            $columnValue,
        ));
    }
}
