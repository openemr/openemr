<?php

declare(strict_types=1);

namespace OpenEMR\Tests\Fixtures;

use OpenEMR\Common\Database\QueryUtils;

abstract class AbstractFixture
{
    public function __construct(
        protected readonly string $recordsTablename,
    ) {
    }

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

    protected function loadRecord(array $record): array
    {
        $record['id'] = QueryUtils::insertOne($this->recordsTablename, $record);

        return $record;
    }

    public function removeFixtureRecords(): void
    {
        foreach ($this->records as $record) {
            $this->removeRecord($record);
        }
    }

    protected function removeRecord(array $record): void
    {
        QueryUtils::removeById($this->recordsTablename, $record['id']);

        unset($this->records[$record['id']]);
    }
}
