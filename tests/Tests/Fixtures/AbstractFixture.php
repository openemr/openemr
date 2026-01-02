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

namespace OpenEMR\Tests\Fixtures;

use Webmozart\Assert\Assert;

/**
 * @template TRecord of array
 */
abstract class AbstractFixture implements FixtureInterface
{
    protected array $records = [];

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

    abstract public function load(): void;

    protected function loadFromFile(string $filename): void
    {
        $records = array_merge($this->records, json_decode(
            file_get_contents($filename),
            true,
            512,
            \JSON_THROW_ON_ERROR,
        ));

        foreach ($records as $record) {
            $record = $this->loadRecord($record);

            if ($this instanceof RemovableFixtureInterface) {
                Assert::keyExists($record, 'id', 'Expected to have ID at record');

                $this->records[$record['id']] = $record;
            } else {
                $this->records[] = $record;
            }
        }
    }

    abstract protected function loadRecord(array $record): array;
}
