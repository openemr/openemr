<?php

/**
 * Isolated TelemetryRepository Test
 *
 * Tests TelemetryRepository functionality without database dependencies.
 * Uses stubs and mocks to test business logic in isolation.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Telemetry;

use OpenEMR\Telemetry\TelemetryRepository;
use PHPUnit\Framework\TestCase;

class TelemetryRepositoryTest extends TestCase
{
    public function testSaveTelemetryEventMethodSignature(): void
    {
        $repository = new TelemetryRepository();

        // Verify method exists and is callable
        $this->assertTrue(method_exists($repository, 'saveTelemetryEvent'));
        $this->assertTrue(is_callable($repository->saveTelemetryEvent(...)));

        // Test method signature
        $reflection = new \ReflectionMethod($repository, 'saveTelemetryEvent');
        $this->assertEquals(2, $reflection->getNumberOfParameters());

        // Test parameter types
        $parameters = $reflection->getParameters();
        $eventDataParam = $parameters[0];
        $currentTimeParam = $parameters[1];

        $this->assertEquals('eventData', $eventDataParam->getName());
        $this->assertEquals('array', $eventDataParam->getType()->getName());

        $this->assertEquals('currentTime', $currentTimeParam->getName());
        $this->assertEquals('string', $currentTimeParam->getType()->getName());

        // Test return type is bool
        $returnType = $reflection->getReturnType();
        $this->assertEquals('bool', $returnType->getName());
    }

    public function testFetchUsageRecordsMethodSignature(): void
    {
        $repository = new TelemetryRepository();

        // Verify method exists and is callable
        $this->assertTrue(method_exists($repository, 'fetchUsageRecords'));
        $this->assertTrue(is_callable($repository->fetchUsageRecords(...)));

        // Test method signature
        $reflection = new \ReflectionMethod($repository, 'fetchUsageRecords');
        $this->assertEquals(0, $reflection->getNumberOfParameters());

        // Test return type is array
        $returnType = $reflection->getReturnType();
        $this->assertEquals('array', $returnType->getName());
    }

    public function testClearTelemetryDataMethodSignature(): void
    {
        $repository = new TelemetryRepository();

        // Verify method exists and is callable
        $this->assertTrue(method_exists($repository, 'clearTelemetryData'));
        $this->assertTrue(is_callable($repository->clearTelemetryData(...)));

        // Test method signature
        $reflection = new \ReflectionMethod($repository, 'clearTelemetryData');
        $this->assertEquals(0, $reflection->getNumberOfParameters());

        // Test return type is void
        $returnType = $reflection->getReturnType();
        $this->assertEquals('void', $returnType->getName());
    }

    public function testSaveTelemetryEventCallsSqlStatementWithCorrectParameters(): void
    {
        // Create a partial mock to mock the sqlStatementThrowException method
        /** @var TelemetryRepository|MockObject $repository */
        $repository = $this->getMockBuilder(TelemetryRepository::class)
            ->onlyMethods(['sqlStatementThrowException'])
            ->getMock();

        $eventData = [
            'eventType' => 'click',
            'eventLabel' => 'test-button',
            'eventUrl' => '/test/url',
            'eventTarget' => 'button'
        ];
        $currentTime = '2025-01-15 10:30:00';

        $expectedSql = "INSERT INTO track_events (event_type, event_label, event_url, event_target, first_event, last_event, label_count)
            VALUES (?, ?, ?, ?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE
              event_url    = ?,
              event_target = ?,
              last_event   = ?,
              label_count  = label_count + 1";

        $expectedParams = [
            'click',
            'test-button',
            '/test/url',
            'button',
            '2025-01-15 10:30:00',
            '2025-01-15 10:30:00',
            1,
            // Update values:
            '/test/url',
            'button',
            '2025-01-15 10:30:00'
        ];

        // Mock sqlStatementThrowException to return success (with noLog: true)
        $repository->expects($this->once())
            ->method('sqlStatementThrowException')
            ->with($expectedSql, $expectedParams, true) // noLog: true
            ->willReturn(123); // Mock return value

        $result = $repository->saveTelemetryEvent($eventData, $currentTime);

        $this->assertTrue($result);
    }

    public function testFetchUsageRecordsCallsFetchRecordsWithNoLog(): void
    {
        // Create a partial mock to mock the fetchRecords method
        /** @var TelemetryRepository|MockObject $repository */
        $repository = $this->getMockBuilder(TelemetryRepository::class)
            ->onlyMethods(['fetchRecords'])
            ->getMock();

        $expectedSql = "SELECT event_type, event_label, event_url, event_target, first_event, last_event, label_count AS count FROM track_events";
        $expectedParams = [];

        $mockData = [
            ['event_type' => 'click', 'event_label' => 'button1', 'count' => 5],
            ['event_type' => 'page_view', 'event_label' => 'dashboard', 'count' => 10]
        ];

        // Mock fetchRecords with noLog: true
        $repository->expects($this->once())
            ->method('fetchRecords')
            ->with($expectedSql, $expectedParams, true) // noLog: true
            ->willReturn($mockData);

        $result = $repository->fetchUsageRecords();

        $this->assertEquals($mockData, $result);
        $this->assertCount(2, $result);
        $this->assertEquals('click', $result[0]['event_type']);
        $this->assertEquals('button1', $result[0]['event_label']);
        $this->assertEquals(5, $result[0]['count']);
    }

    public function testClearTelemetryDataCallsSqlStatementWithNoLog(): void
    {
        // Create a partial mock to mock the sqlStatementThrowException method
        /** @var TelemetryRepository|MockObject $repository */
        $repository = $this->getMockBuilder(TelemetryRepository::class)
            ->onlyMethods(['sqlStatementThrowException'])
            ->getMock();

        $expectedSql = "TRUNCATE track_events";
        $expectedParams = [];

        // Mock sqlStatementThrowException with noLog: true
        $repository->expects($this->once())
            ->method('sqlStatementThrowException')
            ->with($expectedSql, $expectedParams, true) // noLog: true
            ->willReturn(null); // TRUNCATE returns null

        // This method returns void, so we just verify it calls sqlStatementThrowException
        $repository->clearTelemetryData();
    }

    public function testSaveTelemetryEventReturnsFalseWhenSqlStatementFails(): void
    {
        // Create a partial mock to mock the sqlStatementThrowException method
        /** @var TelemetryRepository|MockObject $repository */
        $repository = $this->getMockBuilder(TelemetryRepository::class)
            ->onlyMethods(['sqlStatementThrowException'])
            ->getMock();

        $eventData = [
            'eventType' => 'click',
            'eventLabel' => 'test-button',
            'eventUrl' => '/test/url',
            'eventTarget' => 'button'
        ];
        $currentTime = '2025-01-15 10:30:00';

        // Mock sqlStatementThrowException to return failure (false/0)
        $repository->expects($this->once())
            ->method('sqlStatementThrowException')
            ->willReturn(false);

        $result = $repository->saveTelemetryEvent($eventData, $currentTime);

        $this->assertFalse($result);
    }

    public function testFetchUsageRecordsReturnsEmptyArrayWhenNoData(): void
    {
        // Create a partial mock to mock the fetchRecords method
        /** @var TelemetryRepository|MockObject $repository */
        $repository = $this->getMockBuilder(TelemetryRepository::class)
            ->onlyMethods(['fetchRecords'])
            ->getMock();

        // Mock fetchRecords to return empty array
        $repository->expects($this->once())
            ->method('fetchRecords')
            ->with($this->isType('string'), [], true) // noLog: true
            ->willReturn([]);

        $result = $repository->fetchUsageRecords();

        $this->assertEquals([], $result);
        $this->assertCount(0, $result);
    }
}
