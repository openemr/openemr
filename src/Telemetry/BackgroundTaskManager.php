<?php

namespace OpenEMR\Telemetry;

/**
 * Manages background task operations for telemetry.
 */
class BackgroundTaskManager
{
    /**
     * Creates or updates the background telemetry task.
     *
     * @param string $period Number of days (as a string) for the task interval.
     */
    public static function modifyTelemetryTask(string $period = '33'): void
    {
        $total_minutes = (int)$period * 1440;
        $sql = "SELECT COUNT(*) as count FROM `background_services` WHERE `name` = 'Telemetry_Task'";
        $result = sqlQueryNoLog($sql);
        if ($result['count'] > 0) {
            $sql = "UPDATE `background_services` SET `execute_interval` = ? WHERE `name` = 'Telemetry_Task'";
            sqlStatementNoLog($sql, [$total_minutes]);
            return;
        }
        $sql = "INSERT INTO `background_services` 
                (`name`, `title`, `active`, `running`, `next_run`, `execute_interval`, `function`, `require_once`, `sort_order`) 
                VALUES ('Telemetry_Task', 'Report Scheduled Telemetry', '0', '0', current_timestamp(), '43200', 'reportUsageData', '/library/ajax/track_events.php', '100')";
        sqlStatementNoLog($sql);
    }

    /**
     * Deletes the background telemetry task.
     */
    public static function deleteTelemetryTask(): void
    {
        $sql = "DELETE FROM `background_services` WHERE `name` = 'Telemetry_Task'";
        sqlStatementNoLog($sql);
    }

    /**
     * Enables the background telemetry task.
     */
    public static function enableTelemetryTask(): void
    {
        $sql = "UPDATE `background_services` SET `active` = '1' WHERE `name` = 'Telemetry_Task'";
        sqlStatementNoLog($sql);
    }

    /**
     * Disables the background telemetry task.
     */
    public static function disableTelemetryTask(): void
    {
        $sql = "UPDATE `background_services` SET `active` = '0' WHERE `name` = 'Telemetry_Task'";
        sqlStatementNoLog($sql);
    }
}
