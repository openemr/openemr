<?php

namespace OpenEMR\Tests\Isolated\Telemetry;

use PHPUnit\Framework\TestCase;

class TelemetryRepositoryTest extends TestCase
{
    private TelemetryRepositoryIsolated $repository;

    protected function setUp(): void
    {
        $this->repository = new TelemetryRepositoryIsolated();
    }

    public function testSaveTelemetryEventSuccess(): void
    {
        $eventData = [
            'eventType' => 'click',
            'eventLabel' => 'test_button',
            'eventUrl' => '/test/page',
            'eventTarget' => '_self'
        ];
        $currentTime = '2024-01-01 12:00:00';

        $this->repository->setSqlResult(true);
        $result = $this->repository->saveTelemetryEvent($eventData, $currentTime);

        $this->assertTrue($result);

        $lastQuery = $this->repository->getLastSqlQuery();
        $this->assertStringContainsString('INSERT INTO track_events', $lastQuery);
        $this->assertStringContainsString('ON DUPLICATE KEY UPDATE', $lastQuery);

        $lastParams = $this->repository->getLastSqlParams();
        $this->assertEquals('click', $lastParams[0]);
        $this->assertEquals('test_button', $lastParams[1]);
        $this->assertEquals('/test/page', $lastParams[2]);
        $this->assertEquals('_self', $lastParams[3]);
        $this->assertEquals($currentTime, $lastParams[4]);
        $this->assertEquals($currentTime, $lastParams[5]);
        $this->assertEquals(1, $lastParams[6]);
    }

    public function testSaveTelemetryEventFailure(): void
    {
        $eventData = [
            'eventType' => 'click',
            'eventLabel' => 'test_button',
            'eventUrl' => '/test/page',
            'eventTarget' => '_self'
        ];
        $currentTime = '2024-01-01 12:00:00';

        $this->repository->setSqlResult(false);
        $result = $this->repository->saveTelemetryEvent($eventData, $currentTime);

        $this->assertFalse($result);
    }

    public function testSaveTelemetryEventWithEmptyFields(): void
    {
        $eventData = [
            'eventType' => '',
            'eventLabel' => '',
            'eventUrl' => '',
            'eventTarget' => ''
        ];
        $currentTime = '2024-01-01 12:00:00';

        $this->repository->setSqlResult(true);
        $result = $this->repository->saveTelemetryEvent($eventData, $currentTime);

        $this->assertTrue($result);

        $lastParams = $this->repository->getLastSqlParams();
        $this->assertEquals('', $lastParams[0]);
        $this->assertEquals('', $lastParams[1]);
        $this->assertEquals('', $lastParams[2]);
        $this->assertEquals('', $lastParams[3]);
    }

    public function testFetchUsageRecordsSuccess(): void
    {
        $mockRecords = [
            [
                'event_type' => 'click',
                'event_label' => 'button_1',
                'event_url' => '/page1',
                'event_target' => '_self',
                'first_event' => '2024-01-01 10:00:00',
                'last_event' => '2024-01-01 15:00:00',
                'count' => 5
            ],
            [
                'event_type' => 'api',
                'event_label' => 'patient_create',
                'event_url' => '/api/patient',
                'event_target' => 'endpoint',
                'first_event' => '2024-01-01 11:00:00',
                'last_event' => '2024-01-01 14:00:00',
                'count' => 3
            ]
        ];

        $this->repository->setMockRecords($mockRecords);
        $result = $this->repository->fetchUsageRecords();

        $this->assertCount(2, $result);
        $this->assertEquals('click', $result[0]['event_type']);
        $this->assertEquals('button_1', $result[0]['event_label']);
        $this->assertEquals(5, $result[0]['count']);

        $this->assertEquals('api', $result[1]['event_type']);
        $this->assertEquals('patient_create', $result[1]['event_label']);
        $this->assertEquals(3, $result[1]['count']);

        $lastQuery = $this->repository->getLastSqlQuery();
        $this->assertStringContainsString('SELECT event_type, event_label, event_url, event_target, first_event, last_event, label_count AS count', $lastQuery);
        $this->assertStringContainsString('FROM track_events', $lastQuery);
    }

    public function testFetchUsageRecordsEmpty(): void
    {
        $this->repository->setMockRecords([]);
        $result = $this->repository->fetchUsageRecords();

        $this->assertEmpty($result);
        $this->assertIsArray($result);
    }

    public function testClearTelemetryData(): void
    {
        $this->repository->clearTelemetryData();

        $lastQuery = $this->repository->getLastSqlQuery();
        $this->assertEquals('TRUNCATE track_events', $lastQuery);
        $this->assertTrue($this->repository->wasClearCalled());
    }

    public function testFetchUsageRecordsWithNullValues(): void
    {
        $mockRecords = [
            [
                'event_type' => 'click',
                'event_label' => 'button_test',
                'event_url' => null,
                'event_target' => null,
                'first_event' => '2024-01-01 10:00:00',
                'last_event' => null,
                'count' => 1
            ]
        ];

        $this->repository->setMockRecords($mockRecords);
        $result = $this->repository->fetchUsageRecords();

        $this->assertCount(1, $result);
        $this->assertNull($result[0]['event_url']);
        $this->assertNull($result[0]['event_target']);
        $this->assertNull($result[0]['last_event']);
    }

    public function testSaveTelemetryEventWithLongStrings(): void
    {
        $longString = str_repeat('a', 1000);
        $eventData = [
            'eventType' => $longString,
            'eventLabel' => $longString,
            'eventUrl' => $longString,
            'eventTarget' => $longString
        ];
        $currentTime = '2024-01-01 12:00:00';

        $this->repository->setSqlResult(true);
        $result = $this->repository->saveTelemetryEvent($eventData, $currentTime);

        $this->assertTrue($result);

        $lastParams = $this->repository->getLastSqlParams();
        $this->assertEquals($longString, $lastParams[0]);
        $this->assertEquals($longString, $lastParams[1]);
        $this->assertEquals($longString, $lastParams[2]);
        $this->assertEquals($longString, $lastParams[3]);
    }
}

class TelemetryRepositoryIsolated
{
    private bool $sqlResult = true;
    private string $lastSqlQuery = '';
    private array $lastSqlParams = [];
    private array $mockRecords = [];
    private bool $clearCalled = false;

    public function setSqlResult(bool $result): void
    {
        $this->sqlResult = $result;
    }

    public function setMockRecords(array $records): void
    {
        $this->mockRecords = $records;
    }

    public function getLastSqlQuery(): string
    {
        return $this->lastSqlQuery;
    }

    public function getLastSqlParams(): array
    {
        return $this->lastSqlParams;
    }

    public function wasClearCalled(): bool
    {
        return $this->clearCalled;
    }

    public function saveTelemetryEvent(array $eventData, string $currentTime): bool
    {
        $this->lastSqlQuery = "INSERT INTO track_events (event_type, event_label, event_url, event_target, first_event, last_event, label_count)
            VALUES (?, ?, ?, ?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE
              event_url    = ?,
              event_target = ?,
              last_event   = ?,
              label_count  = label_count + 1";

        $this->lastSqlParams = [
            $eventData['eventType'],
            $eventData['eventLabel'],
            $eventData['eventUrl'],
            $eventData['eventTarget'],
            $currentTime,
            $currentTime,
            1,
            $eventData['eventUrl'],
            $eventData['eventTarget'],
            $currentTime
        ];

        return $this->sqlResult;
    }

    public function fetchUsageRecords(): array
    {
        $this->lastSqlQuery = "SELECT event_type, event_label, event_url, event_target, first_event, last_event, label_count AS count FROM track_events";
        return $this->mockRecords;
    }

    public function clearTelemetryData(): void
    {
        $this->lastSqlQuery = "TRUNCATE track_events";
        $this->clearCalled = true;
    }
}
