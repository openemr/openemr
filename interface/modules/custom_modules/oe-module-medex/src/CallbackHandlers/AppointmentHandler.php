<?php
/**
 * MedEx Callback - Appointment Handler
 *
 * Handles appointment-related callback requests from MedEx.
 * Supports incremental sync via changed_since (uses pc_time) and
 * lightweight status snapshots via getStatusChanges().
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    MedEx <support@MedExBank.com>
 * @copyright Copyright (c) 2018-2026 MedEx
 * @license   Proprietary - All Rights Reserved
 */

namespace OpenEMR\Modules\MedEx\CallbackHandlers;

use OpenEMR\Common\Database\QueryUtils;

class AppointmentHandler
{
    /**
     * Get appointments for a date range, with optional incremental sync.
     *
     * Supports:
     *   - Full pull: start_date + end_date (defaults: today → +90 days)
     *   - Incremental: changed_since=YYYY-MM-DD HH:MM:SS (returns only appts
     *     whose pc_time >= that timestamp, within the date window)
     *   - Facility filter: facility_id (optional)
     *
     * Returns all fields needed by MedEx's loadAppts / campaign matching.
     *
     * @param array<string,mixed> $params
     * @return array<string,mixed>
     */
    public function getAppointments(array $params): array
    {
        $start_date = $params['start_date'] ?? date('Y-m-d', strtotime('-1 day'));
        $end_date = $params['end_date'] ?? date('Y-m-d', strtotime('+90 days'));
        $changed_since = $params['changed_since'] ?? null;
        $facility_id = $params['facility_id'] ?? null;

        // Build query — return all fields MedEx needs for campaign matching
        $sql = "SELECT
                    pc.pc_eid,
                    pc.pc_pid,
                    pc.pc_aid,
                    pc.pc_catid,
                    pc.pc_title,
                    pc.pc_time,
                    pc.pc_hometext,
                    pc.pc_eventDate,
                    pc.pc_endDate,
                    pc.pc_startTime,
                    pc.pc_endTime,
                    pc.pc_duration,
                    pc.pc_apptstatus,
                    pc.pc_facility,
                    pc.pc_recurrtype,
                    pc.pc_recurrspec,
                    p.pid,
                    p.fname,
                    p.mname,
                    p.lname,
                    p.phone_cell,
                    p.phone_home,
                    p.email,
                    p.street,
                    p.city,
                    p.state,
                    p.postal_code,
                    p.country_code,
                    p.language,
                    p.hipaa_allowsms,
                    p.hipaa_allowemail,
                    p.hipaa_voice,
                    u.id as provider_id,
                    u.fname as provider_fname,
                    u.lname as provider_lname,
                    u.npi as provider_npi,
                    f.name as facility_name
                FROM openemr_postcalendar_events pc
                LEFT JOIN patient_data p ON pc.pc_pid = p.pid
                LEFT JOIN users u ON pc.pc_aid = u.id
                LEFT JOIN facility f ON pc.pc_facility = f.id
                WHERE pc.pc_eventDate >= ?
                AND pc.pc_eventDate <= ?
                AND pc.pc_pid IS NOT NULL
                AND pc.pc_pid != ''
                AND pc.pc_pid != '0'";

        $params_array = [$start_date, $end_date];

        // Incremental sync: only return appointments modified since given timestamp
        if (!empty($changed_since)) {
            $sql .= " AND pc.pc_time >= ?";
            $params_array[] = $changed_since;
        }

        if ($facility_id) {
            $sql .= " AND pc.pc_facility = ?";
            $params_array[] = $facility_id;
        }

        $sql .= " ORDER BY pc.pc_eventDate, pc.pc_startTime";

        $rows = QueryUtils::fetchRecords($sql, $params_array);

        $appointments = [];
        foreach ($rows as $row) {
            $appointments[] = [
                // Appointment fields
                'pc_eid'         => $row['pc_eid'],
                'pc_pid'         => $row['pc_pid'] ?? $row['pid'],
                'pc_aid'         => $row['pc_aid'],
                'pc_catid'       => $row['pc_catid'],
                'pc_title'       => $row['pc_title'],
                'pc_time'        => $row['pc_time'],
                'pc_hometext'    => $row['pc_hometext'],
                'pc_eventDate'   => $row['pc_eventDate'],
                'pc_endDate'     => $row['pc_endDate'],
                'pc_startTime'   => $row['pc_startTime'],
                'pc_endTime'     => $row['pc_endTime'],
                'pc_duration'    => $row['pc_duration'],
                'pc_apptstatus'  => $row['pc_apptstatus'],
                'pc_facility'    => $row['pc_facility'],
                'pc_recurrtype'  => $row['pc_recurrtype'],
                'pc_recurrspec'  => $row['pc_recurrspec'],
                // Patient fields (split names, full contact + consent)
                'fname'          => $row['fname'],
                'mname'          => $row['mname'],
                'lname'          => $row['lname'],
                'phone_cell'     => $row['phone_cell'],
                'phone_home'     => $row['phone_home'],
                'email'          => $row['email'],
                'street'         => $row['street'],
                'city'           => $row['city'],
                'state'          => $row['state'],
                'postal_code'    => $row['postal_code'],
                'country_code'   => $row['country_code'],
                'language'       => $row['language'],
                'hipaa_allowsms' => $row['hipaa_allowsms'],
                'hipaa_allowemail' => $row['hipaa_allowemail'],
                'hipaa_voice'    => $row['hipaa_voice'],
                // Provider fields
                'provider_id'    => $row['provider_id'],
                'provider_fname' => $row['provider_fname'],
                'provider_lname' => $row['provider_lname'],
                'provider_npi'   => $row['provider_npi'],
                // Facility
                'facility_name'  => $row['facility_name'],
            ];
        }

        return [
            'success' => true,
            'count' => count($appointments),
            'sync_timestamp' => date('Y-m-d H:i:s'),
            'appointments' => $appointments
        ];
    }

    /**
     * Lightweight status snapshot — returns only pc_eid + pc_apptstatus
     * for all appointments in the active window.
     *
     * MedEx compares these against hipaa_cal_events.e_apptstatus and
     * updates only the diffs. Catches status changes (confirmed, arrived,
     * cancelled) that don't update pc_time.
     *
     * @param array<string,mixed> $params
     * @return array<string,mixed>
     */
    public function getStatusChanges(array $params): array
    {
        $start_date = $params['start_date'] ?? date('Y-m-d');
        $end_date = $params['end_date'] ?? date('Y-m-d', strtotime('+90 days'));

        $sql = "SELECT pc_eid, pc_apptstatus, pc_eventDate, pc_aid, pc_facility
                FROM openemr_postcalendar_events
                WHERE pc_eventDate >= ?
                AND pc_eventDate <= ?
                AND pc_pid IS NOT NULL
                AND pc_pid != ''
                AND pc_pid != '0'
                ORDER BY pc_eventDate";

        $rows = QueryUtils::fetchRecords($sql, [$start_date, $end_date]);

        $statuses = [];
        foreach ($rows as $row) {
            $statuses[] = [
                'pc_eid'        => $row['pc_eid'],
                'pc_apptstatus' => $row['pc_apptstatus'],
                'pc_eventDate'  => $row['pc_eventDate'],
                'pc_aid'        => $row['pc_aid'],
                'pc_facility'   => $row['pc_facility'],
            ];
        }

        return [
            'success' => true,
            'count' => count($statuses),
            'statuses' => $statuses
        ];
    }

    /**
     * Update appointment status (e.g., confirmed by patient)
     *
     * @param array<string,mixed> $params
     * @return array<string,mixed>
     */
    public function updateAppointmentStatus(array $params): array
    {
        $appointment_id = $params['appointment_id'] ?? null;
        $status = $params['status'] ?? null;

        if (!$appointment_id || !$status) {
            return [
                'success' => false,
                'error' => 'Missing appointment_id or status'
            ];
        }

        // Update appointment
        $sql = "UPDATE openemr_postcalendar_events
                SET pc_apptstatus = ?
                WHERE pc_eid = ?";

        QueryUtils::sqlStatementThrowException($sql, [$status, $appointment_id]);

        // Log the update
        $log_sql = "INSERT INTO medex_outgoing
                    (msg_pc_eid, msg_reply, msg_date)
                    VALUES (?, ?, NOW())";
        QueryUtils::sqlStatementThrowException($log_sql, [$appointment_id, $status]);

        return [
            'success' => true,
            'message' => 'Appointment status updated',
            'appointment_id' => $appointment_id,
            'new_status' => $status
        ];
    }

    /**
     * Consume an available slot when a patient books an appointment.
     * Links the patient appointment to the slot and marks it as consumed.
     *
     * @param array<string,mixed> $params
     * @return array<string,mixed>
     */
    public function consumeSlot(array $params): array
    {
        $this->ensureSlotRegistryTable();

        $patient_pc_eid = (int)($params['patient_pc_eid'] ?? 0);
        $provider_id = (int)($params['provider_id'] ?? 0);
        $event_date = trim((string)($params['event_date'] ?? ''));
        $start_time = trim((string)($params['start_time'] ?? ''));
        $category_id = (int)($params['category_id'] ?? 0);

        if ($patient_pc_eid <= 0 || $provider_id <= 0 || $event_date === '' || $start_time === '') {
            return ['success' => false, 'error' => 'Missing required parameters'];
        }

        // Find the matching open slot (MEDEX generated, no patient assigned)
        $slot_sql = "SELECT pc_eid, pc_endTime, pc_duration, pc_location
                     FROM openemr_postcalendar_events
                     WHERE pc_aid = ?
                       AND pc_eventDate = ?
                       AND pc_startTime = ?
                       AND pc_catid = ?
                       AND (COALESCE(pc_pid,'') = '' OR pc_pid = '0')
                       AND pc_apptstatus = '-'
                       AND pc_title LIKE 'Open Slot%'
                     ORDER BY pc_eid DESC
                     LIMIT 1";
        $slot_row = QueryUtils::querySingleRow($slot_sql, [$provider_id, $event_date, $start_time, $category_id]);

        $open_slot_eid = (int)($slot_row['pc_eid'] ?? 0);
        $is_reschedulable = true;

        // If no matching MEDEX slot found, this is a manual booking - still track it
        if ($open_slot_eid === 0) {
            // Check if appointment already has a slot record (re-consumption case)
            $existing = QueryUtils::querySingleRow(
                "SELECT slot_id, slot_state FROM medex_slot_registry WHERE patient_pc_eid = ?",
                [$patient_pc_eid]
            );
            if (!empty($existing['slot_id'])) {
                // Update existing record back to consumed
                QueryUtils::sqlStatementThrowException(
                    "UPDATE medex_slot_registry
                     SET slot_state = 'consumed', consumed_at = NOW(), released_at = NULL
                     WHERE slot_id = ?",
                    [(int)$existing['slot_id']]
                );
                return [
                    'success' => true,
                    'slot_consumed' => true,
                    'slot_id' => (int)$existing['slot_id'],
                    'patient_pc_eid' => $patient_pc_eid,
                    'previous_state' => $existing['slot_state'] ?? 'unknown'
                ];
            }
        }

        // Check for existing slot record first
        $existing_slot = QueryUtils::querySingleRow(
            "SELECT slot_id FROM medex_slot_registry WHERE patient_pc_eid = ?",
            [$patient_pc_eid]
        );

        if (!empty($existing_slot['slot_id'])) {
            // Slot already tracked, just ensure it's marked consumed
            QueryUtils::sqlStatementThrowException(
                "UPDATE medex_slot_registry
                 SET slot_state = 'consumed', open_slot_eid = ?, consumed_at = NOW(), released_at = NULL
                 WHERE slot_id = ?",
                [$open_slot_eid, (int)$existing_slot['slot_id']]
            );
            return [
                'success' => true,
                'slot_consumed' => true,
                'slot_id' => (int)$existing_slot['slot_id'],
                'patient_pc_eid' => $patient_pc_eid,
                'open_slot_eid' => $open_slot_eid
            ];
        }

        // Parse slot_source from pc_location if present
        $slot_source = 'manual';
        if (!empty($slot_row['pc_location'])) {
            $location = (string)$slot_row['pc_location'];
            if (strpos($location, 'MEDEX_') === 0) {
                $slot_source = 'medex';
            }
        }

        // Create new slot registry entry
        $duration = (int)($slot_row['pc_duration'] ?? 900) / 60; // seconds to minutes
        $end_time = (string)($slot_row['pc_endTime'] ?? '');

        QueryUtils::sqlStatementThrowException(
            "INSERT INTO medex_slot_registry
             (open_slot_eid, patient_pc_eid, provider_id, facility_id, event_date, start_time, end_time,
              category_id, duration_minutes, slot_source, slot_state, reschedulable, consumed_at)
             VALUES (?, ?, ?, 0, ?, ?, ?, ?, ?, ?, 'consumed', ?, NOW())",
            [
                $open_slot_eid,
                $patient_pc_eid,
                $provider_id,
                $event_date,
                $start_time,
                $end_time,
                $category_id,
                $duration,
                $slot_source,
                $is_reschedulable ? 1 : 0
            ]
        );

        $new_slot_id = (int)QueryUtils::fetchSingleValue("SELECT LAST_INSERT_ID()", 'LAST_INSERT_ID()', []);

        // Delete or mark the original open slot event as consumed
        if ($open_slot_eid > 0) {
            QueryUtils::sqlStatementThrowException(
                "UPDATE openemr_postcalendar_events
                 SET pc_apptstatus = 'F', pc_title = CONCAT('[CONSUMED] ', pc_title)
                 WHERE pc_eid = ?",
                [$open_slot_eid]
            );
        }

        return [
            'success' => true,
            'slot_consumed' => true,
            'slot_id' => $new_slot_id,
            'patient_pc_eid' => $patient_pc_eid,
            'open_slot_eid' => $open_slot_eid,
            'slot_source' => $slot_source
        ];
    }

    /**
     * Release a consumed slot back to available pool.
     * Called when appointment is moved, cancelled, or deleted.
     *
     * @param array<string,mixed> $params
     * @return array<string,mixed>
     */
    public function releaseSlot(array $params): array
    {
        $this->ensureSlotRegistryTable();

        $patient_pc_eid = (int)($params['patient_pc_eid'] ?? 0);
        $create_available_slot = (bool)($params['create_available_slot'] ?? true);

        if ($patient_pc_eid <= 0) {
            return ['success' => false, 'error' => 'Missing patient_pc_eid'];
        }

        // Get current slot info
        $slot_row = QueryUtils::querySingleRow(
            "SELECT slot_id, open_slot_eid, provider_id, event_date, start_time, end_time,
                    category_id, duration_minutes, reschedulable
             FROM medex_slot_registry
             WHERE patient_pc_eid = ?
             ORDER BY slot_id DESC
             LIMIT 1",
            [$patient_pc_eid]
        );

        if (empty($slot_row['slot_id'])) {
            return [
                'success' => true,
                'slot_released' => false,
                'message' => 'No slot registry entry found for this appointment'
            ];
        }

        $slot_id = (int)$slot_row['slot_id'];
        $was_reschedulable = (bool)($slot_row['reschedulable'] ?? false);

        // Mark slot as released
        QueryUtils::sqlStatementThrowException(
            "UPDATE medex_slot_registry
             SET slot_state = 'released', patient_pc_eid = NULL, released_at = NOW()
             WHERE slot_id = ?",
            [$slot_id]
        );

        $new_open_slot_eid = null;

        // Re-create an available slot event if requested and slot was reschedulable
        if ($create_available_slot && $was_reschedulable) {
            $provider_id = (int)$slot_row['provider_id'];
            $event_date = (string)$slot_row['event_date'];
            $start_time = (string)$slot_row['start_time'];
            $end_time = (string)$slot_row['end_time'];
            $category_id = (int)$slot_row['category_id'];
            $duration_seconds = (int)$slot_row['duration_minutes'] * 60;

            // Check if slot already exists (shouldn't, but safety check)
            $existing = QueryUtils::querySingleRow(
                "SELECT pc_eid FROM openemr_postcalendar_events
                 WHERE pc_aid = ? AND pc_eventDate = ? AND pc_startTime = ?
                   AND pc_catid = ? AND (COALESCE(pc_pid,'') = '' OR pc_pid = '0')",
                [$provider_id, $event_date, $start_time, $category_id]
            );

            if (empty($existing['pc_eid'])) {
                $facility_id = 0;
                $title = 'Open Slot - Released';

                $new_open_slot_eid = sqlInsert(
                    "INSERT INTO openemr_postcalendar_events
                     (pc_catid, pc_multiple, pc_aid, pc_pid, pc_title, pc_time, pc_hometext,
                      pc_eventDate, pc_endDate, pc_duration, pc_startTime, pc_endTime,
                      pc_alldayevent, pc_apptstatus, pc_eventstatus, pc_prefcatid, pc_location)
                     VALUES (?, 0, ?, '', ?, NOW(), '', ?, ?, ?, ?, ?, 0, '-', 1, ?, 'MEDEX_RELEASED')",
                    [
                        $category_id, $provider_id, $title, $event_date, $event_date,
                        $duration_seconds, $start_time, $end_time, $category_id
                    ]
                );

                // Update registry with new open slot reference
                if ($new_open_slot_eid > 0) {
                    QueryUtils::sqlStatementThrowException(
                        "UPDATE medex_slot_registry SET open_slot_eid = ? WHERE slot_id = ?",
                        [$new_open_slot_eid, $slot_id]
                    );
                }
            } else {
                $new_open_slot_eid = (int)$existing['pc_eid'];
            }
        }

        return [
            'success' => true,
            'slot_released' => true,
            'slot_id' => $slot_id,
            'was_reschedulable' => $was_reschedulable,
            'new_open_slot_eid' => $new_open_slot_eid,
            'create_available_slot' => $create_available_slot
        ];
    }

    /**
     * Atomic move operation: release old slot + consume new slot.
     * Called when patient or secretary reschedules to a new time.
     *
     * @param array<string,mixed> $params
     * @return array<string,mixed>
     */
    public function moveAppointment(array $params): array
    {
        $this->ensureSlotRegistryTable();

        $patient_pc_eid = (int)($params['patient_pc_eid'] ?? 0);
        $old_provider_id = (int)($params['old_provider_id'] ?? 0);
        $old_event_date = trim((string)($params['old_event_date'] ?? ''));
        $old_start_time = trim((string)($params['old_start_time'] ?? ''));

        $new_provider_id = (int)($params['new_provider_id'] ?? 0);
        $new_event_date = trim((string)($params['new_event_date'] ?? ''));
        $new_start_time = trim((string)($params['new_start_time'] ?? ''));
        $new_category_id = (int)($params['new_category_id'] ?? 0);

        if ($patient_pc_eid <= 0 || $new_provider_id <= 0 || $new_event_date === '' || $new_start_time === '') {
            return ['success' => false, 'error' => 'Missing required move parameters'];
        }

        // Step 1: Release the old slot (make it available again)
        $release_result = $this->releaseSlot([
            'patient_pc_eid' => $patient_pc_eid,
            'create_available_slot' => true
        ]);

        // Step 2: Update the appointment in OpenEMR
        // Note: The actual pc_eid stays the same, but time/provider changes
        QueryUtils::sqlStatementThrowException(
            "UPDATE openemr_postcalendar_events
             SET pc_aid = ?, pc_eventDate = ?, pc_startTime = ?, pc_catid = ?
             WHERE pc_eid = ?",
            [$new_provider_id, $new_event_date, $new_start_time, $new_category_id, $patient_pc_eid]
        );

        // Recalculate end time based on duration
        $duration_row = QueryUtils::querySingleRow(
            "SELECT pc_duration FROM openemr_postcalendar_events WHERE pc_eid = ?",
            [$patient_pc_eid]
        );
        $duration_seconds = (int)($duration_row['pc_duration'] ?? 900);
        $new_end_time = date('H:i:s', strtotime($new_start_time) + $duration_seconds);

        QueryUtils::sqlStatementThrowException(
            "UPDATE openemr_postcalendar_events SET pc_endTime = ? WHERE pc_eid = ?",
            [$new_end_time, $patient_pc_eid]
        );

        // Step 3: Consume the new slot
        $consume_result = $this->consumeSlot([
            'patient_pc_eid' => $patient_pc_eid,
            'provider_id' => $new_provider_id,
            'event_date' => $new_event_date,
            'start_time' => $new_start_time,
            'category_id' => $new_category_id
        ]);

        return [
            'success' => true,
            'moved' => true,
            'patient_pc_eid' => $patient_pc_eid,
            'release_result' => $release_result,
            'consume_result' => $consume_result,
            'old_slot' => [
                'provider_id' => $old_provider_id,
                'event_date' => $old_event_date,
                'start_time' => $old_start_time
            ],
            'new_slot' => [
                'provider_id' => $new_provider_id,
                'event_date' => $new_event_date,
                'start_time' => $new_start_time,
                'end_time' => $new_end_time,
                'category_id' => $new_category_id
            ]
        ];
    }

    /**
     * Find rescheduling options for a patient appointment.
     * Returns available slots matching the same category, within provider rules.
     *
     * @param array<string,mixed> $params
     * @return array<string,mixed>
     */
    public function getReschedulingOptions(array $params): array
    {
        $this->ensureSlotRegistryTable();

        $patient_pc_eid = (int)($params['patient_pc_eid'] ?? 0);
        $provider_id = (int)($params['provider_id'] ?? 0);
        $preferred_category_id = (int)($params['preferred_category_id'] ?? 0);
        $max_offers = min(12, max(1, (int)($params['max_offers'] ?? 5)));
        $min_hours_before = max(0, (int)($params['min_hours_before'] ?? 1));
        $max_days_before = max(0, (int)($params['max_days_before'] ?? 30));
        $max_days_after = max(0, (int)($params['max_days_after'] ?? 60));
        $allow_same_day = (bool)($params['allow_same_day'] ?? false);

        // Get current appointment details if patient_pc_eid provided
        if ($patient_pc_eid > 0 && ($provider_id === 0 || $preferred_category_id === 0)) {
            $appt_row = QueryUtils::querySingleRow(
                "SELECT pc_aid, pc_catid, pc_eventDate, pc_startTime
                 FROM openemr_postcalendar_events WHERE pc_eid = ?",
                [$patient_pc_eid]
            );
            if (!empty($appt_row)) {
                $provider_id = $provider_id ?: (int)$appt_row['pc_aid'];
                $preferred_category_id = $preferred_category_id ?: (int)$appt_row['pc_catid'];
            }
        }

        if ($provider_id <= 0) {
            return ['success' => false, 'error' => 'Provider ID required'];
        }

        // Calculate date boundaries
        $now = time();
        $today = date('Y-m-d', $now);
        $earliest_date = date('Y-m-d', strtotime("+{$min_hours_before} hours", $now));
        if (!$allow_same_day && $earliest_date === $today) {
            $earliest_date = date('Y-m-d', strtotime('+1 day', $now));
        }
        $latest_date = date('Y-m-d', strtotime("+{$max_days_after} days", $now));

        // Find available slots
        $sql = "SELECT
                    pc_eid,
                    pc_eventDate as date,
                    pc_startTime as start,
                    pc_endTime as end,
                    pc_catid as category_id,
                    pc_duration,
                    pc_facility as facility_id,
                    pc_title
                FROM openemr_postcalendar_events
                WHERE pc_aid = ?
                  AND pc_eventDate >= ?
                  AND pc_eventDate <= ?
                  AND (COALESCE(pc_pid,'') = '' OR pc_pid = '0')
                  AND pc_apptstatus = '-'";

        $query_params = [$provider_id, $earliest_date, $latest_date];

        if ($preferred_category_id > 0) {
            $sql .= " AND pc_catid = ?";
            $query_params[] = $preferred_category_id;
        }

        $sql .= " ORDER BY pc_eventDate ASC, pc_startTime ASC LIMIT ?";
        $query_params[] = $max_offers;

        $rows = QueryUtils::fetchRecords($sql, $query_params);

        $options = [];
        foreach ($rows as $row) {
            $options[] = [
                'pc_eid' => (int)$row['pc_eid'],
                'date' => (string)$row['date'],
                'start_time' => (string)$row['start'],
                'end_time' => (string)$row['end'],
                'category_id' => (int)$row['category_id'],
                'duration_minutes' => (int)$row['pc_duration'] / 60,
                'facility_id' => (int)$row['facility_id'],
                'title' => (string)$row['pc_title']
            ];
        }

        // Also include the currently held slot in results (marked as current)
        $current_slot = null;
        if ($patient_pc_eid > 0) {
            $current = QueryUtils::querySingleRow(
                "SELECT pc_eid, pc_eventDate, pc_startTime, pc_endTime, pc_catid, pc_facility
                 FROM openemr_postcalendar_events WHERE pc_eid = ?",
                [$patient_pc_eid]
            );
            if (!empty($current)) {
                $current_slot = [
                    'pc_eid' => $patient_pc_eid,
                    'date' => (string)$current['pc_eventDate'],
                    'start_time' => (string)$current['pc_startTime'],
                    'end_time' => (string)$current['pc_endTime'],
                    'category_id' => (int)$current['pc_catid'],
                    'facility_id' => (int)$current['pc_facility'],
                    'is_current' => true
                ];
            }
        }

        return [
            'success' => true,
            'provider_id' => $provider_id,
            'preferred_category_id' => $preferred_category_id,
            'date_range' => ['earliest' => $earliest_date, 'latest' => $latest_date],
            'options_count' => count($options),
            'options' => $options,
            'current_slot' => $current_slot,
            'rules_applied' => [
                'max_offers' => $max_offers,
                'min_hours_before' => $min_hours_before,
                'max_days_before' => $max_days_before,
                'max_days_after' => $max_days_after,
                'allow_same_day' => $allow_same_day
            ]
        ];
    }

    /**
     * Ensure medex_slot_registry table exists.
     */
    private function ensureSlotRegistryTable(): void
    {
        sqlStatement("CREATE TABLE IF NOT EXISTS medex_slot_registry (
            slot_id INT UNSIGNED NOT NULL AUTO_INCREMENT,
            open_slot_eid INT UNSIGNED NULL,
            patient_pc_eid INT UNSIGNED NULL,
            provider_id INT UNSIGNED NOT NULL,
            facility_id INT UNSIGNED NOT NULL DEFAULT 0,
            event_date DATE NOT NULL,
            start_time TIME NOT NULL,
            end_time TIME NULL,
            category_id INT UNSIGNED NOT NULL,
            duration_minutes INT UNSIGNED NOT NULL DEFAULT 15,
            slot_source VARCHAR(32) NOT NULL DEFAULT 'medex',
            slot_state ENUM('available','consumed','released','expired') NOT NULL DEFAULT 'available',
            reschedulable TINYINT NOT NULL DEFAULT 1,
            consumed_at DATETIME NULL,
            released_at DATETIME NULL,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (slot_id),
            KEY idx_open_slot (open_slot_eid),
            KEY idx_patient_slot (patient_pc_eid),
            KEY idx_provider_date (provider_id, event_date, slot_state),
            KEY idx_category (category_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    }
}
