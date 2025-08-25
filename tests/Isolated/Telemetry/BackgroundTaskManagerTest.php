<?php

namespace OpenEMR\Tests\Isolated\Telemetry;

use PHPUnit\Framework\TestCase;

class BackgroundTaskManagerTest extends TestCase
{
    private BackgroundTaskManagerIsolated $taskManager;

    protected function setUp(): void
    {
        $this->taskManager = new BackgroundTaskManagerIsolated();
    }

    public function testModifyTelemetryTaskCreatesNewTask(): void
    {
        $this->taskManager->setTaskExists(false);

        $this->taskManager->modifyTelemetryTask();

        $insertQuery = $this->taskManager->getLastInsertQuery();
        $this->assertStringContainsString('INSERT INTO `background_services`', $insertQuery);
        $this->assertStringContainsString('Telemetry_Task', $insertQuery);
        $this->assertStringContainsString('Report Scheduled Telemetry', $insertQuery);
        $this->assertStringContainsString('/library/telemetry_reporting_service.php', $insertQuery);
        $this->assertStringContainsString('reportTelemetryTask', $insertQuery);

        $insertParams = $this->taskManager->getLastInsertParams();
        $expectedMinutes = 33 * 1440; // 33 days in minutes
        $this->assertEquals($expectedMinutes, $insertParams[0]);
    }

    public function testModifyTelemetryTaskUpdatesExistingTask(): void
    {
        $this->taskManager->setTaskExists(true);

        $this->taskManager->modifyTelemetryTask();

        $updateQuery = $this->taskManager->getLastUpdateQuery();
        $this->assertStringContainsString('UPDATE `background_services`', $updateQuery);
        $this->assertStringContainsString('SET `execute_interval` = ?', $updateQuery);
        $this->assertStringContainsString("WHERE `name` = 'Telemetry_Task'", $updateQuery);

        $updateParams = $this->taskManager->getLastUpdateParams();
        $expectedMinutes = 33 * 1440; // 33 days in minutes
        $this->assertEquals($expectedMinutes, $updateParams[0]);
    }

    public function testModifyTelemetryTaskCalculatesCorrectInterval(): void
    {
        $this->taskManager->setTaskExists(false);

        $this->taskManager->modifyTelemetryTask();

        $insertParams = $this->taskManager->getLastInsertParams();
        $actualMinutes = $insertParams[0];
        $expectedMinutes = 33 * 24 * 60; // 33 days * 24 hours * 60 minutes

        $this->assertEquals($expectedMinutes, $actualMinutes);
        $this->assertEquals(47520, $actualMinutes); // 33 * 1440
    }

    public function testDeleteTelemetryTask(): void
    {
        $this->taskManager->deleteTelemetryTask();

        $deleteQuery = $this->taskManager->getLastDeleteQuery();
        $this->assertStringContainsString('DELETE FROM `background_services`', $deleteQuery);
        $this->assertStringContainsString("WHERE `name` = 'Telemetry_Task'", $deleteQuery);
    }

    public function testEnableTelemetryTask(): void
    {
        $this->taskManager->enableTelemetryTask();

        $updateQuery = $this->taskManager->getLastUpdateQuery();
        $this->assertStringContainsString('UPDATE `background_services`', $updateQuery);
        $this->assertStringContainsString("SET `active` = '1'", $updateQuery);
        $this->assertStringContainsString("WHERE `name` = 'Telemetry_Task'", $updateQuery);
    }

    public function testDisableTelemetryTask(): void
    {
        $this->taskManager->disableTelemetryTask();

        $updateQuery = $this->taskManager->getLastUpdateQuery();
        $this->assertStringContainsString('UPDATE `background_services`', $updateQuery);
        $this->assertStringContainsString("SET `active` = '0'", $updateQuery);
        $this->assertStringContainsString("WHERE `name` = 'Telemetry_Task'", $updateQuery);
    }

    public function testModifyTelemetryTaskWithExistingTaskCount(): void
    {
        $this->taskManager->setTaskCount(1);
        $this->taskManager->setTaskExists(true);

        $this->taskManager->modifyTelemetryTask();

        $countQuery = $this->taskManager->getLastCountQuery();
        $this->assertStringContainsString('SELECT COUNT(*) as count', $countQuery);
        $this->assertStringContainsString('FROM `background_services`', $countQuery);
        $this->assertStringContainsString("WHERE `name` = 'Telemetry_Task'", $countQuery);

        $this->assertNotNull($this->taskManager->getLastUpdateQuery());
        $this->assertNull($this->taskManager->getLastInsertQuery());
    }

    public function testModifyTelemetryTaskWithZeroTaskCount(): void
    {
        $this->taskManager->setTaskCount(0);
        $this->taskManager->setTaskExists(false);

        $this->taskManager->modifyTelemetryTask();

        $this->assertNotNull($this->taskManager->getLastInsertQuery());
        $this->assertNull($this->taskManager->getLastUpdateQuery());
    }

    public function testInsertTaskHasCorrectDefaultValues(): void
    {
        $this->taskManager->setTaskExists(false);

        $this->taskManager->modifyTelemetryTask();

        $insertQuery = $this->taskManager->getLastInsertQuery();

        $this->assertStringContainsString("'0'", $insertQuery); // active = 0
        $this->assertStringContainsString("'0'", $insertQuery); // running = 0
        $this->assertStringContainsString('current_timestamp()', $insertQuery); // next_run
        $this->assertStringContainsString("'100'", $insertQuery); // sort_order = 100
    }

    public function testTaskNameConsistency(): void
    {
        $expectedTaskName = 'Telemetry_Task';

        $this->taskManager->modifyTelemetryTask();
        $this->assertStringContainsString($expectedTaskName, $this->taskManager->getLastCountQuery());

        $this->taskManager->deleteTelemetryTask();
        $this->assertStringContainsString($expectedTaskName, $this->taskManager->getLastDeleteQuery());

        $this->taskManager->enableTelemetryTask();
        $this->assertStringContainsString($expectedTaskName, $this->taskManager->getLastUpdateQuery());

        $this->taskManager->disableTelemetryTask();
        $this->assertStringContainsString($expectedTaskName, $this->taskManager->getLastUpdateQuery());
    }

    public function testMultipleOperationsOnSameTask(): void
    {
        $this->taskManager->disableTelemetryTask();
        $disabledQuery = $this->taskManager->getLastUpdateQuery();
        $this->assertStringContainsString("SET `active` = '0'", $disabledQuery);

        $this->taskManager->enableTelemetryTask();
        $enabledQuery = $this->taskManager->getLastUpdateQuery();
        $this->assertStringContainsString("SET `active` = '1'", $enabledQuery);

        $this->assertNotEquals($disabledQuery, $enabledQuery);
    }
}

class BackgroundTaskManagerIsolated
{
    private bool $taskExists = false;
    private int $taskCount = 0;
    private ?string $lastCountQuery = null;
    private ?string $lastInsertQuery = null;
    private ?array $lastInsertParams = null;
    private ?string $lastUpdateQuery = null;
    private ?array $lastUpdateParams = null;
    private ?string $lastDeleteQuery = null;

    public function setTaskExists(bool $exists): void
    {
        $this->taskExists = $exists;
        $this->taskCount = $exists ? 1 : 0;
    }

    public function setTaskCount(int $count): void
    {
        $this->taskCount = $count;
        $this->taskExists = $count > 0;
    }

    public function getLastCountQuery(): ?string
    {
        return $this->lastCountQuery;
    }

    public function getLastInsertQuery(): ?string
    {
        return $this->lastInsertQuery;
    }

    public function getLastInsertParams(): ?array
    {
        return $this->lastInsertParams;
    }

    public function getLastUpdateQuery(): ?string
    {
        return $this->lastUpdateQuery;
    }

    public function getLastUpdateParams(): ?array
    {
        return $this->lastUpdateParams;
    }

    public function getLastDeleteQuery(): ?string
    {
        return $this->lastDeleteQuery;
    }

    public function modifyTelemetryTask(): void
    {
        $total_minutes = 33 * 1440;

        $sql = "SELECT COUNT(*) as count FROM `background_services` WHERE `name` = 'Telemetry_Task'";
        $this->lastCountQuery = $sql;

        $result = ['count' => $this->taskCount];

        if ($result['count'] > 0) {
            $sql = "UPDATE `background_services` SET `execute_interval` = ? WHERE `name` = 'Telemetry_Task'";
            $this->lastUpdateQuery = $sql;
            $this->lastUpdateParams = [$total_minutes];
            $this->lastInsertQuery = null;
            $this->lastInsertParams = null;
            return;
        }

        $sql = "INSERT INTO `background_services`
                (`name`, `title`, `active`, `running`, `next_run`, `execute_interval`, `function`, `require_once`, `sort_order`)
                VALUES ('Telemetry_Task', 'Report Scheduled Telemetry', '0', '0', current_timestamp(), ?, 'reportTelemetryTask', '/library/telemetry_reporting_service.php', '100')";
        $this->lastInsertQuery = $sql;
        $this->lastInsertParams = [$total_minutes];
        $this->lastUpdateQuery = null;
        $this->lastUpdateParams = null;
    }

    public function deleteTelemetryTask(): void
    {
        $sql = "DELETE FROM `background_services` WHERE `name` = 'Telemetry_Task'";
        $this->lastDeleteQuery = $sql;
    }

    public function enableTelemetryTask(): void
    {
        $sql = "UPDATE `background_services` SET `active` = '1' WHERE `name` = 'Telemetry_Task'";
        $this->lastUpdateQuery = $sql;
        $this->lastUpdateParams = null;
    }

    public function disableTelemetryTask(): void
    {
        $sql = "UPDATE `background_services` SET `active` = '0' WHERE `name` = 'Telemetry_Task'";
        $this->lastUpdateQuery = $sql;
        $this->lastUpdateParams = null;
    }
}
