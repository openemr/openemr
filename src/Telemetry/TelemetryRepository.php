<?php

/**
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2025 - 2026 <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Telemetry;

use OpenEMR\Common\Database\DatabaseQueryTrait;

class TelemetryRepository
{
    use DatabaseQueryTrait;

    /**
     * Inserts a new click event or updates an existing one.
     */
    public function saveTelemetryEvent(array $eventData, string $currentTime): bool
    {
        // For API events, we don't want to check and count existing records unless unique scopes.
        // This is to allow for different scopes across duplicate endpoints.

        $sql = "INSERT INTO track_events (event_type, event_label, event_url, event_target, first_event, last_event, label_count)
            VALUES (?, ?, ?, ?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE
              event_url    = ?,
              event_target = ?,
              last_event   = ?,
              label_count  = label_count + 1";

        $params = [
            $eventData['eventType'],
            $eventData['eventLabel'],
            $eventData['eventUrl'],
            $eventData['eventTarget'],
            $currentTime,
            $currentTime,
            1,
            // Update values:
            $eventData['eventUrl'],
            $eventData['eventTarget'],
            $currentTime
        ];

        return (bool)$this->sqlStatementThrowException($sql, $params, noLog: true);
    }

    /**
     * Retrieves all usage records from the telemetry table.
     */
    public function fetchUsageRecords(): array
    {
        // Alias label_count as count for backward compatibility.
        $sql = "SELECT event_type, event_label, event_url, event_target, first_event, last_event, label_count AS count FROM track_events";
        return $this->fetchRecords($sql, [], noLog: true);
    }

    /**
     * Clears the telemetry data table.
     */
    public function clearTelemetryData(): void
    {
        $sql = "TRUNCATE track_events";
        $this->sqlStatementThrowException($sql, [], noLog: true);
    }

    /**
     * Fetches site population data including total patients, portal patients, and active users.
     * Create by Claude.ai integrated by sjpadgett
     *
     * @return array
     */
    public function fetchSitePopulationData(): array
    {
        $population = [];

        // Total patients
        $sql = "SELECT COUNT(*) AS total_patients FROM patient_data";
        $result = $this->fetchRecords($sql, [], noLog: true);
        $population['total_patients'] = $result[0]['total_patients'] ?? 0;

        // Total portal patients (where allow_patient_portal is enabled)
        $sql = "SELECT COUNT(*) AS total_portal_patients FROM patient_data WHERE allow_patient_portal = 'YES'";
        $result = $this->fetchRecords($sql, [], noLog: true);
        $population['total_portal_patients'] = $result[0]['total_portal_patients'] ?? 0;

        // Total active users
        $sql = "SELECT COUNT(*) AS total_users FROM users WHERE active = 1 AND username IS NOT NULL AND fname IS NOT NULL";
        $result = $this->fetchRecords($sql, [], noLog: true);
        $population['total_users'] = $result[0]['total_users'] ?? 0;

        // Active users grouped by abook_type
        $sql = <<<'SQL'
        SELECT abook_type, COUNT(*) AS user_count
            FROM users WHERE active = 1
                AND username IS NOT NULL
                AND fname IS NOT NULL
            GROUP BY abook_type
        SQL;
        $result = $this->fetchRecords($sql, [], noLog: true);
        $population['users_by_type'] = $result;

        return $population;
    }

    /**
     * Fetches data about enabled modules.
     *  Create by Claude.ai integrated by sjpadgett
     *
     * @return array
     */
    public function fetchActiveModuleCounts(): array
    {
        $modulesData = [];

        // Total enabled modules
        $sql = "SELECT COUNT(*) AS total_enabled_modules FROM modules WHERE mod_active = 1";
        $result = $this->fetchRecords($sql, [], noLog: true);
        $modulesData['total_enabled_modules'] = $result[0]['total_enabled_modules'] ?? 0;

        // Count of enabled custom modules (type = 0)
        $sql = "SELECT COUNT(*) AS custom_modules FROM modules WHERE mod_active = 1 AND type = 0";
        $result = $this->fetchRecords($sql, [], noLog: true);
        $modulesData['custom_modules'] = $result[0]['custom_modules'] ?? 0;

        // Count of enabled Laminas modules (type = 1)
        $sql = "SELECT COUNT(*) AS laminas_modules FROM modules WHERE mod_active = 1 AND type = 1";
        $result = $this->fetchRecords($sql, [], noLog: true);
        $modulesData['laminas_modules'] = $result[0]['laminas_modules'] ?? 0;

        return $modulesData;
    }
}
