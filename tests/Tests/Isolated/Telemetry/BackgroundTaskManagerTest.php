<?php

namespace OpenEMR\Tests\Isolated\Telemetry;

use OpenEMR\Telemetry\BackgroundTaskManager;
use PHPUnit\Framework\TestCase;

class BackgroundTaskManagerTest extends TestCase
{
    private BackgroundTaskManagerStub $taskManager;

    protected function setUp(): void
    {
        $this->taskManager = new BackgroundTaskManagerStub();
    }

    public function testModifyTelemetryTaskUpdatesExistingTask(): void
    {
        $this->taskManager->setMockFetchSingleValue(5);

        $this->taskManager->modifyTelemetryTask();

        $expectedSql = "UPDATE `background_services` SET `execute_interval` = ? WHERE `name` = 'Telemetry_Task'";
        $this->assertEquals($expectedSql, $this->taskManager->getLastSql());
        $this->assertEquals([47520], $this->taskManager->getLastBinds());
    }

    public function testModifyTelemetryTaskCreatesNewTask(): void
    {
        $this->taskManager->setMockFetchSingleValue(null);

        $this->taskManager->modifyTelemetryTask();

        $expectedSql = "INSERT INTO `background_services`
                (`name`, `title`, `active`, `running`, `next_run`, `execute_interval`, `function`, `require_once`, `sort_order`)
                VALUES ('Telemetry_Task', 'Report Scheduled Telemetry', '0', '0', current_timestamp(), ?, 'reportTelemetryTask', '/library/telemetry_reporting_service.php', '100')";
        $this->assertEquals($expectedSql, $this->taskManager->getLastSql());
        $this->assertEquals([47520], $this->taskManager->getLastBinds());
    }

    public function testDeleteTelemetryTask(): void
    {
        $this->taskManager->deleteTelemetryTask();

        $expectedSql = "DELETE FROM `background_services` WHERE `name` = 'Telemetry_Task'";
        $this->assertEquals($expectedSql, $this->taskManager->getLastSql());
        $this->assertEquals([], $this->taskManager->getLastBinds());
    }

    public function testEnableTelemetryTask(): void
    {
        $this->taskManager->enableTelemetryTask();

        $expectedSql = "UPDATE `background_services` SET `active` = '1' WHERE `name` = 'Telemetry_Task'";
        $this->assertEquals($expectedSql, $this->taskManager->getLastSql());
        $this->assertEquals([], $this->taskManager->getLastBinds());
    }

    public function testDisableTelemetryTask(): void
    {
        $this->taskManager->disableTelemetryTask();

        $expectedSql = "UPDATE `background_services` SET `active` = '0' WHERE `name` = 'Telemetry_Task'";
        $this->assertEquals($expectedSql, $this->taskManager->getLastSql());
        $this->assertEquals([], $this->taskManager->getLastBinds());
    }
}

class BackgroundTaskManagerStub extends BackgroundTaskManager
{
    private $mockFetchSingleValue = null;
    private $lastSql = '';
    private $lastBinds = [];

    public function setMockFetchSingleValue($value): void
    {
        $this->mockFetchSingleValue = $value;
    }

    public function getLastSql(): string
    {
        return $this->lastSql;
    }

    public function getLastBinds(): array
    {
        return $this->lastBinds;
    }

    protected function fetchSingleValue($sqlStatement, $column, $binds = [])
    {
        $this->lastSql = $sqlStatement;
        $this->lastBinds = $binds;
        return $this->mockFetchSingleValue;
    }

    protected function fetchRecordsNoLog($sqlStatement, $binds)
    {
        $this->lastSql = $sqlStatement;
        $this->lastBinds = $binds;
        return [];
    }
}
