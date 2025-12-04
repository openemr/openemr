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
}
