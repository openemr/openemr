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

abstract class AbstractFixture
{
    protected array $records = [];

    public function getRecords(): array
    {
        return $this->records;
    }

    public function getRandomRecord(): array
    {
        $index = array_rand($this->records, 1);

        return $this->records[$index];
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

        $this->records = [];
        foreach ($records as $record) {
            $record = $this->loadRecord($record);
            $this->records[$record['id']] = $record;
        }
    }

    abstract protected function loadRecord(array $record): array;

    public function removeFixtureRecords(): void
    {
        foreach ($this->records as $record) {
            $this->removeRecord($record);

            unset($this->records[$record['id']]);
        }
    }

    abstract protected function removeRecord(array $record): void;
}
