<?php

/**
 * MedEx Calendar Service
 *
 * Handles calendar business logic, interfacing with OpenEMR calendar tables
 * while providing modern REST API access and MedEx enhancements.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    MedEx <https://medexbank.com>
 * @copyright Copyright (c) 2026 MedEx
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Modules\MedEx\Services;

class CalendarService
{
    /**
     * Get events for FullCalendar format
     *
     * @param string $start Start date (Y-m-d)
     * @param string $end End date (Y-m-d)
     * @param int|null $providerId Provider filter
     * @param int|null $facilityId Facility filter
     * @return array Events in FullCalendar format
     */
    public function getEvents(string $start, string $end, ?int $providerId = null, ?int $facilityId = null): array
    {
        $sql = "SELECT
            e.pc_eid as id,
            e.pc_title as title,
            e.pc_eventDate as date,
            e.pc_startTime as start_time,
            e.pc_endTime as end_time,
            e.pc_duration,
            e.pc_alldayevent,
            e.pc_catid,
            e.pc_aid as provider_id,
            e.pc_pid as patient_id,
            e.pc_apptstatus,
            e.pc_prefcatid,
            cat.pc_catname,
            cat.pc_catcolor as color,
            CONCAT(p.fname, ' ', p.lname) as patient_name,
            CONCAT(u.fname, ' ', u.lname) as provider_name
        FROM openemr_postcalendar_events e
        LEFT JOIN openemr_postcalendar_categories cat ON e.pc_catid = cat.pc_catid
        LEFT JOIN patient_data p ON e.pc_pid = p.pid
        LEFT JOIN users u ON e.pc_aid = u.id
        WHERE e.pc_eventDate BETWEEN ? AND ?";

        $params = [$start, $end];

        if ($providerId) {
            $sql .= " AND e.pc_aid = ?";
            $params[] = $providerId;
        }

        if ($facilityId) {
            $sql .= " AND e.pc_facility = ?";
            $params[] = $facilityId;
        }

        $sql .= " ORDER BY e.pc_eventDate, e.pc_startTime";

        $result = sqlStatement($sql, $params);

        $events = [];
        while ($row = sqlFetchArray($result)) {
            $events[] = $this->formatEventForFullCalendar($row);
        }

        return $events;
    }

    /**
     * Format database event for FullCalendar
     */
    private function formatEventForFullCalendar(array $row): array
    {
        // Combine date and time for ISO format
        $start = $row['date'] . 'T' . $row['start_time'];

        // Calculate end time - use pc_endTime if valid, otherwise calculate from duration
        $endTime = $row['end_time'];
        $duration = (int)($row['pc_duration'] ?? 0);

        // If end_time is missing, same as start, or invalid, calculate from duration
        if (empty($endTime) || $endTime === '00:00:00' || $endTime === $row['start_time']) {
            if ($duration > 0) {
                $startTimestamp = strtotime($row['date'] . ' ' . $row['start_time']);
                $endTimestamp = $startTimestamp + $duration;
                $endTime = date('H:i:s', $endTimestamp);
            } else {
                // Default to 15 minutes if no duration
                $endTime = date('H:i:s', strtotime($row['start_time'] . ' +15 minutes'));
            }
        }

        $end = $row['date'] . 'T' . $endTime;

        // Build event object
        $event = [
            'id' => $row['id'],
            'title' => $this->buildEventTitle($row),
            'start' => $start,
            'end' => $end,
            'allDay' => (bool)$row['pc_alldayevent'],
            'backgroundColor' => '#' . $row['color'],
            'borderColor' => '#' . $row['color'],
            'extendedProps' => [
                'patient_id' => $row['patient_id'],
                'patient_name' => $row['patient_name'],
                'provider_id' => $row['provider_id'],
                'provider_name' => $row['provider_name'],
                'category_id' => $row['pc_catid'],
                'category_name' => $row['pc_catname'],
                'status' => $row['pc_apptstatus'],
                'preferred_category' => $row['pc_prefcatid'],
                'preferredCategoryId' => $row['pc_prefcatid'],
                'isGeneratedSlot' => empty($row['patient_id']) && strpos($row['pc_catname'], 'Open Slot') !== false,
            ]
        ];

        // Add MedEx enhancements
        $event['extendedProps']['medex'] = $this->getMedExEnhancements($row);

        return $event;
    }

    /**
     * Build event title with patient name or category
     */
    private function buildEventTitle(array $row): string
    {
        if ($row['patient_name']) {
            return $row['patient_name'] . ' - ' . $row['pc_catname'];
        }

        return $row['pc_catname'];
    }

    /**
     * Get MedEx-specific enhancements for event
     */
    private function getMedExEnhancements(array $row): array
    {
        $enhancements = [
            'communication_status' => null,
            'noshow_risk' => 0.0,
            'revenue_estimate' => null,
        ];

        // Only add MedEx data for patient appointments (not In Office blocks)
        if (!$row['patient_id']) {
            return $enhancements;
        }

        // Check for MedEx communication status
        $commStatus = sqlQuery(
            "SELECT msg_type, msg_reply
             FROM medex_outgoing
             WHERE msg_pc_eid = ?
             ORDER BY msg_date DESC LIMIT 1",
            [$row['id']]
        );

        if ($commStatus) {
            $enhancements['communication_status'] = [
                'type' => $commStatus['msg_type'],
                'reply' => $commStatus['msg_reply']
            ];
        }

        // TODO: Call MedExBank API for AI predictions (keep proprietary)
        // $enhancements['noshow_risk'] = $this->medexAPI->predictNoShow($row);

        return $enhancements;
    }

    /**
     * Create new event
     */
    public function createEvent(array $data): array
    {
        $eventDate = $data['date'] ?? date('Y-m-d');
        $startTime = $data['start_time'] ?? '09:00:00';
        $duration = $data['duration'] ?? 15;

        // Calculate end time
        $endTime = date('H:i:s', strtotime($startTime) + ($duration * 60));

        $sql = "INSERT INTO openemr_postcalendar_events SET
            pc_catid = ?,
            pc_aid = ?,
            pc_pid = ?,
            pc_title = ?,
            pc_time = NOW(),
            pc_hometext = ?,
            pc_eventDate = ?,
            pc_endDate = ?,
            pc_duration = ?,
            pc_startTime = ?,
            pc_endTime = ?,
            pc_alldayevent = ?,
            pc_apptstatus = ?,
            pc_prefcatid = ?,
            pc_facility = ?";

        $result = sqlInsert($sql, [
            $data['category_id'] ?? 5,
            $data['provider_id'],
            $data['patient_id'] ?? 0,
            $data['title'] ?? '',
            $data['comments'] ?? '',
            $eventDate,
            $eventDate,
            $duration * 60,
            $startTime,
            $endTime,
            $data['all_day'] ?? 0,
            $data['status'] ?? '-',
            $data['preferred_category'] ?? 0,
            $data['facility_id'] ?? 0
        ]);

        return [
            'success' => true,
            'event_id' => $result,
            'message' => 'Event created successfully'
        ];
    }

    /**
     * Update event (for drag-drop reschedule)
     */
    public function updateEvent(int $eventId, array $data): array
    {
        $updates = [];
        $params = [];

        if (isset($data['date'])) {
            $updates[] = "pc_eventDate = ?";
            $params[] = $data['date'];
        }

        if (isset($data['start_time'])) {
            $updates[] = "pc_startTime = ?";
            $params[] = $data['start_time'];

            // Recalculate end time if duration exists
            $duration = $this->getEventDuration($eventId);
            if ($duration) {
                $endTime = date('H:i:s', strtotime($data['start_time']) + ($duration * 60));
                $updates[] = "pc_endTime = ?";
                $params[] = $endTime;
            }
        }

        if (isset($data['provider_id'])) {
            $updates[] = "pc_aid = ?";
            $params[] = $data['provider_id'];
        }

        if (empty($updates)) {
            return ['success' => false, 'message' => 'No fields to update'];
        }

        $sql = "UPDATE openemr_postcalendar_events SET " . implode(', ', $updates) . " WHERE pc_eid = ?";
        $params[] = $eventId;

        sqlStatement($sql, $params);

        return [
            'success' => true,
            'message' => 'Event updated successfully'
        ];
    }

    /**
     * Get event duration in minutes
     */
    private function getEventDuration(int $eventId): ?int
    {
        $result = sqlQuery(
            "SELECT pc_duration FROM openemr_postcalendar_events WHERE pc_eid = ?",
            [$eventId]
        );

        return $result ? round($result['pc_duration'] / 60) : null;
    }

    /**
     * Delete event
     */
    public function deleteEvent(int $eventId): array
    {
        sqlStatement(
            "DELETE FROM openemr_postcalendar_events WHERE pc_eid = ?",
            [$eventId]
        );

        return [
            'success' => true,
            'message' => 'Event deleted successfully'
        ];
    }

    /**
     * Get providers for dropdown
     */
    public function getProviders(?int $facilityId = null): array
    {
        $sql = "SELECT
            u.id,
            CONCAT(u.fname, ' ', u.lname) as name,
            u.calendar
        FROM users u
        WHERE u.authorized = 1
        AND u.active = 1
        AND u.calendar = 1";

        if ($facilityId) {
            $sql .= " AND u.facility_id = ?";
            $result = sqlStatement($sql, [$facilityId]);
        } else {
            $result = sqlStatement($sql);
        }

        $providers = [];
        while ($row = sqlFetchArray($result)) {
            $providers[] = $row;
        }

        return $providers;
    }
}
