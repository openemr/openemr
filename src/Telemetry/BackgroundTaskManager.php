<?php

/**
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2025 <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Telemetry;

use OpenEMR\Common\Database\DatabaseQueryTrait;

/**
 * Manages background task operations for telemetry.
 */
class BackgroundTaskManager
{
    use DatabaseQueryTrait;

    /**
     * Creates or updates the background telemetry task.
     */
    public function modifyTelemetryTask(): void
    {
        $total_minutes = 33 * 1440;

        $sql = "SELECT COUNT(*) as count FROM `background_services` WHERE `name` = 'Telemetry_Task'";
        $result = $this->fetchSingleValue($sql, 'count');
        // If the task does not exist, create it.
        if (!is_null($result)) {
            $sql = "UPDATE `background_services` SET `execute_interval` = ? WHERE `name` = 'Telemetry_Task'";
            $this->fetchRecordsNoLog($sql, [$total_minutes]);
            return;
        }
        $sql = "INSERT INTO `background_services`
                (`name`, `title`, `active`, `running`, `next_run`, `execute_interval`, `function`, `require_once`, `sort_order`)
                VALUES ('Telemetry_Task', 'Report Scheduled Telemetry', '0', '0', current_timestamp(), ?, 'reportTelemetryTask', '/library/telemetry_reporting_service.php', '100')";
        $this->fetchRecordsNoLog($sql, [$total_minutes]);
    }

    /**
     * Deletes the background telemetry task.
     */
    public function deleteTelemetryTask(): void
    {
        $sql = "DELETE FROM `background_services` WHERE `name` = 'Telemetry_Task'";
        $this->fetchRecordsNoLog($sql, []);
    }

    /**
     * Enables the background telemetry task.
     */
    public function enableTelemetryTask(): void
    {
        $sql = "UPDATE `background_services` SET `active` = '1' WHERE `name` = 'Telemetry_Task'";
        $this->fetchRecordsNoLog($sql, []);
    }

    /**
     * Disables the background telemetry task.
     */
    public function disableTelemetryTask(): void
    {
        $sql = "UPDATE `background_services` SET `active` = '0' WHERE `name` = 'Telemetry_Task'";
        $this->fetchRecordsNoLog($sql, []);
    }
}
