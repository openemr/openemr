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
}
