<?php

/**
 * MedEx Message Generation Service
 *
 * Processes appointments and recalls to generate message requests to MedEx
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    MedEx <support@MedExBank.com>
 * @copyright Copyright (c) 2024-2026 MedEx
 * @license   Proprietary - All Rights Reserved
 */

namespace OpenEMR\Modules\MedEx\Services;

use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Common\Database\SqlQueryException;
use OpenEMR\Core\OEGlobalsBag;

class MessageGenerationService
{
    private \OpenEMR\Modules\MedEx\MedExAPI $medexApi;

    public function __construct(\OpenEMR\Modules\MedEx\MedExAPI $medexApi)
    {
        $this->medexApi = $medexApi;
    }

    /**
     * Generate message requests for campaign events
     *
     * @param string $token API token
     * @param array<array<string,mixed>> $events Campaign events from MedEx
     * @return array<string,mixed> Results of message generation
     */
    public function generate(string $token, array $events): array
    {
        if (empty($events)) {
            return [
                'success' => false,
                'error' => 'No campaign events provided'
            ];
        }

        $results = [
            'appointments' => 0,
            'recalls' => 0,
            'recurrents' => 0,
            'announcements' => 0,
            'surveys' => 0,
            'clinical_reminders' => 0,
            'gogreen' => 0,
            'messages_generated' => []
        ];

        // Get icons for message status display
        $icons = $this->getMessageIcons();

        // Get preferences
        $prefsRecords = QueryUtils::fetchRecords("SELECT * FROM medex_prefs ORDER BY MedEx_lastupdated DESC LIMIT 1");
        $prefs = $prefsRecords[0] ?? null;

        if (!$prefs) {
            return [
                'success' => false,
                'error' => 'No MedEx preferences found'
            ];
        }

        foreach ($events as $event) {
            try {
                $messages = $this->processEvent($event, $prefs, $icons);
                $results['messages_generated'] = array_merge($results['messages_generated'], $messages);

                // Count by message group
                if ($event['M_group'] == 'REMINDER') {
                    $results['appointments'] += count($messages);
                } elseif ($event['M_group'] == 'RECALL') {
                    $results['recalls'] += count($messages);
                } elseif ($event['M_group'] == 'ANNOUNCE') {
                    $results['announcements'] += count($messages);
                } elseif ($event['M_group'] == 'SURVEY') {
                    $results['surveys'] += count($messages);
                } elseif ($event['M_group'] == 'CLINICAL_REMINDER') {
                    $results['clinical_reminders'] += count($messages);
                } elseif ($event['M_group'] == 'GOGREEN') {
                    $results['gogreen'] += count($messages);
                }
            } catch (\Exception $e) {
                error_log("MedEx Message Generation Error: " . $e->getMessage());
                continue;
            }
        }

        // Send messages to MedEx
        if (!empty($results['messages_generated'])) {
            $sendResult = $this->sendMessagesToMedEx($token, $results['messages_generated']);
            $results['send_result'] = $sendResult;
        }

        $results['success'] = true;
        $results['total_messages'] = count($results['messages_generated']);

        return $results;
    }

    /**
     * Process a single campaign event
     *
     * @param array<string,mixed> $event
     * @param array<string,mixed> $prefs
     * @param array<string,mixed> $icons
     * @return array<array<string,mixed>>
     */
    private function processEvent(array $event, array $prefs, array $icons): array
    {
        $messages = [];

        // Build language filter
        $targetLang = '';
        $escapedArr = [];
        if (!empty($event['E_language']) && $event['E_language'] != 'all') {
            $langs = explode('|', $event['E_language']);
            $buildLangs = '';
            foreach ($langs as $lang) {
                if ($lang == 'No preference') {
                    $buildLangs .= "pat.language = '' OR ";
                } else {
                    $buildLangs .= "pat.language=? OR ";
                    $escapedArr[] = $lang;
                }
            }
            $buildLangs = rtrim($buildLangs, 'OR ');
            $targetLang = "(" . $buildLangs . ") AND ";
        }

        // Process based on message group type
        if ($event['M_group'] == 'REMINDER') {
            $messages = $this->processAppointmentReminders($event, $prefs, $targetLang, $escapedArr);
        } elseif ($event['M_group'] == 'RECALL') {
            $messages = $this->processRecalls($event, $prefs, $targetLang, $escapedArr);
        } elseif ($event['M_group'] == 'ANNOUNCE') {
            $messages = $this->processAnnouncements($event, $prefs);
        } elseif ($event['M_group'] == 'SURVEY') {
            $messages = $this->processSurveys($event, $prefs);
        } elseif ($event['M_group'] == 'CLINICAL_REMINDER') {
            $messages = $this->processClinicalReminders($event, $prefs);
        } elseif ($event['M_group'] == 'GOGREEN') {
            $messages = $this->processGoGreen($event, $prefs, $targetLang, $escapedArr);
        }

        return $messages;
    }

    /**
     * Process appointment reminders
     *
     * @param array<string,mixed> $event
     * @param array<string,mixed> $prefs
     * @param string $targetLang
     * @param array<string> $escapedArr
     * @return array<array<string,mixed>>
     */
    private function processAppointmentReminders(array $event, array $prefs, string $targetLang, array $escapedArr): array
    {
        $messages = [];

        // Determine timing and status filters
        if ($event['time_order'] > '0') {
            $interval = '+';
            $apptStatus = ($event['E_instructions'] == 'stop')
                ? " AND pc_apptstatus='-'"
                : " AND pc_apptstatus != '%' AND pc_apptstatus != 'x'";
        } else {
            $interval = '-';
            $apptStatus = " AND pc_apptstatus IN (SELECT option_id FROM list_options WHERE toggle_setting_2='1' AND list_id='apptstat') AND pc_apptstatus != '%' AND pc_apptstatus != 'x'";
        }

        $timing = (int)$event['E_fire_time'] - 1;
        $today = date('l');

        // Skip weekends
        if ($today == 'Sunday' || $today == 'Saturday') {
            return [];
        }

        $timing2 = ($today == 'Friday') ? ($timing + 3) . ':0:1' : ($timing + 1) . ':1:1';

        if (empty($prefs['ME_facilities'])) {
            return [];
        }

        $places = str_replace('|', ',', $prefs['ME_facilities']);

        $query = "SELECT cal.*, pat.*,
                        pat.fname, pat.lname, pat.mname, pat.phone_cell, pat.phone_home, pat.email,
                        pat.hipaa_allowsms, pat.hipaa_allowemail, pat.hipaa_voice, pat.language,
                        fac.name AS facility_name, fac.phone AS facility_phone
                  FROM openemr_postcalendar_events AS cal
                  LEFT JOIN patient_data AS pat ON cal.pc_pid = pat.pid
                  LEFT JOIN facility AS fac ON cal.pc_facility = fac.id
                  WHERE " . $targetLang . "
                        (
                          (
                            pc_eventDate > CURDATE() " . $interval . " INTERVAL " . $timing . " DAY AND
                            pc_eventDate < CURDATE() " . $interval . " INTERVAL '" . $timing2 . "' DAY_MINUTE
                          )
                          OR
                          (
                            pc_eventDate <= CURDATE() " . $interval . " INTERVAL '" . $timing2 . "' DAY_MINUTE AND
                            pc_endDate >= CURDATE() " . $interval . " INTERVAL " . $timing . " DAY AND
                            pc_recurrtype > '0'
                          )
                        )
                        " . $apptStatus . "
                        AND pat.pid > ''
                        AND cal.pc_facility IN (" . $places . ")
                        AND (
                          (pat.hipaa_allowsms='YES' AND pat.phone_cell > '') OR
                          (pat.hipaa_allowemail='YES' AND pat.email > '') OR
                          (pat.hipaa_voice='YES' AND (pat.phone_home > '' OR pat.phone_cell > ''))
                        )
                  ORDER BY pc_eventDate, pc_startTime";

        $appointments = QueryUtils::fetchRecords($query, $escapedArr);

        foreach ($appointments as $appt) {
            // Create message for this appointment
            $message = [
                'pc_eid' => $appt['pc_eid'],
                'patient_id' => $appt['pid'],
                'patient_name' => $appt['fname'] . ' ' . $appt['lname'],
                'appointment_date' => $appt['pc_eventDate'],
                'appointment_time' => $appt['pc_startTime'],
                'facility' => $appt['facility_name'],
                'event_type' => $event['M_type'],
                'event_id' => $event['id'],
                'campaign_uid' => $event['campaign_uid'],
                'modality' => $this->determineModality($appt, $event),
                'phone' => $this->getPatientPhone($appt),
                'email' => $appt['email'] ?? '',
                'language' => $appt['language'] ?? 'English'
            ];

            // Insert into medex_outgoing as "To Send"
            $this->insertOutgoingMessage($message);

            $messages[] = $message;
        }

        return $messages;
    }

    /**
     * Process recalls
     *
     * @param array<string,mixed> $event
     * @param array<string,mixed> $prefs
     * @param string $targetLang
     * @param array<string> $escapedArr
     * @return array<array<string,mixed>>
     */
    private function processRecalls(array $event, array $prefs, string $targetLang, array $escapedArr): array
    {
        $messages = [];
        $interval = ($event['E_timing'] ?? 'BEFORE') === 'BEFORE' ? '-' : '+';
        $timing = $event['E_fire_time'] ?? '0';

        // Get recalls from medex_recalls table
        $query = "SELECT recall.*, pat.*,
                         fac.name AS facility_name
                  FROM medex_recalls AS recall
                  LEFT JOIN patient_data AS pat ON recall.r_pid = pat.pid
                  LEFT JOIN facility AS fac ON recall.r_facility = fac.id
                  WHERE (recall.r_eventDate < CURDATE() " . $interval . " INTERVAL " . add_escape_custom($timing) . " DAY)
                        " . $targetLang . "
                        AND pat.pid > ''
                        AND (
                          (pat.hipaa_allowsms='YES' AND pat.phone_cell > '') OR
                          (pat.hipaa_allowemail='YES' AND pat.email > '') OR
                          (pat.hipaa_voice='YES' AND (pat.phone_home > '' OR pat.phone_cell > ''))
                        )
                  ORDER BY recall.r_eventDate";

        $recalls = QueryUtils::fetchRecords($query, $escapedArr);

        foreach ($recalls as $recall) {
            // Check if recall already has appointment scheduled
            $apptCheck = sqlQuery(
                "SELECT pc_eid FROM openemr_postcalendar_events
                 WHERE pc_pid = ? AND pc_eventDate >= CURDATE()
                 ORDER BY pc_eventDate LIMIT 1",
                [$recall['r_pid']]
            );

            if (!empty($apptCheck['pc_eid'])) {
                // Patient already has appointment, mark recall as complete
                sqlStatement("DELETE FROM medex_recalls WHERE r_pid = ?", [$recall['r_pid']]);
                sqlStatement("DELETE FROM medex_outgoing WHERE msg_pc_eid = ?", ['recall_' . $recall['r_pid']]);
                continue;
            }

            // Create message for this recall
            $message = [
                'pc_eid' => 'recall_' . $recall['r_pid'],
                'patient_id' => $recall['r_pid'],
                'patient_name' => $recall['fname'] . ' ' . $recall['lname'],
                'recall_date' => $recall['r_eventDate'],
                'recall_reason' => $recall['r_reason'] ?? '',
                'facility' => $recall['facility_name'],
                'event_type' => $event['M_type'],
                'event_id' => $event['id'],
                'campaign_uid' => $event['campaign_uid'],
                'modality' => $this->determineModality($recall, $event),
                'phone' => $this->getPatientPhone($recall),
                'email' => $recall['email'] ?? '',
                'language' => $recall['language'] ?? 'English'
            ];

            // Insert into medex_outgoing as "To Send"
            $this->insertOutgoingMessage($message);

            $messages[] = $message;
        }

        return $messages;
    }

    /**
     * Process announcements
     *
     * @param array<string,mixed> $event
     * @param array<string,mixed> $prefs
     * @return array<array<string,mixed>>
     */
    private function processAnnouncements(array $event, array $prefs): array
    {
        $messages = [];

        if (empty($event['appts_start'])) {
            return $messages;
        }

        $today = strtotime(date('Y-m-d'));
        $start = strtotime((string)$event['appts_start']);
        $end = strtotime((string)$event['appts_end']);

        // Only process if current date is within announcement window
        if ($today < $start || $today > $end) {
            return $messages;
        }

        // Build target dates filter
        $target_dates = "cal.pc_eventDate >= '" . add_escape_custom($event['appts_start']) . "' AND
                        cal.pc_eventDate <= '" . add_escape_custom($event['appts_end']) . "'";

        // Build appointment status filter
        $appt_status = $this->buildApptStatusFilter($event);

        // Build providers filter
        $providers = $this->buildProvidersFilter($event, $prefs);

        // Build facilities filter
        $places = $this->buildFacilitiesFilter($event, $prefs);

        // Build visit types filter
        $visit_types = $this->buildVisitTypesFilter($event);

        $escapedArr = [];

        $query = "SELECT cal.*, pat.*,
                         fac.name AS facility_name,
                         u.fname AS provider_fname, u.lname AS provider_lname
                  FROM openemr_postcalendar_events AS cal
                  LEFT JOIN patient_data AS pat ON cal.pc_pid = pat.pid
                  LEFT JOIN facility AS fac ON cal.pc_facility = fac.id
                  LEFT JOIN users AS u ON cal.pc_aid = u.id
                  WHERE " . $target_dates . "
                        " . $appt_status . "
                        " . $providers . "
                        " . $places . "
                        " . $visit_types . "
                        AND pat.pid > ''
                        AND (
                          (pat.hipaa_allowsms='YES' AND pat.phone_cell > '') OR
                          (pat.hipaa_allowemail='YES' AND pat.email > '') OR
                          (pat.hipaa_voice='YES' AND (pat.phone_home > '' OR pat.phone_cell > ''))
                        )
                  ORDER BY cal.pc_eventDate, cal.pc_startTime";

        $appointments = QueryUtils::fetchRecords($query, $escapedArr);

        foreach ($appointments as $appt) {
            // Create message for this appointment
            $message = [
                'pc_eid' => $appt['pc_eid'],
                'patient_id' => $appt['pid'],
                'patient_name' => $appt['fname'] . ' ' . $appt['lname'],
                'appointment_date' => $appt['pc_eventDate'],
                'appointment_time' => $appt['pc_startTime'],
                'facility' => $appt['facility_name'],
                'provider' => ($appt['provider_fname'] ?? '') . ' ' . ($appt['provider_lname'] ?? ''),
                'event_type' => $event['M_type'],
                'event_id' => $event['id'],
                'campaign_uid' => $event['campaign_uid'],
                'modality' => $this->determineModality($appt, $event),
                'phone' => $this->getPatientPhone($appt),
                'email' => $appt['email'] ?? '',
                'language' => $appt['language'] ?? 'English'
            ];

            // Insert into medex_outgoing as "To Send"
            $this->insertOutgoingMessage($message);

            $messages[] = $message;
        }

        return $messages;
    }

    /**
     * Process surveys
     *
     * @param array<string,mixed> $event
     * @param array<string,mixed> $prefs
     * @return array<array<string,mixed>>
     */
    private function processSurveys(array $event, array $prefs): array
    {
        $messages = [];
        $timing = $event['timing'] ?? '180';

        // Build appointment status filter for completed appointments
        // Surveys are sent after completed appointments based on toggle_setting2=1 in list_options
        $escapedArr = [];
        $appt_status = "AND cal.pc_apptstatus IN (
                            SELECT option_id FROM list_options
                            WHERE list_id = 'apptstat'
                            AND toggle_setting_2 = '1'
                            AND activity = 1
                        )";

        // Build providers filter
        $providers = $this->buildProvidersFilter($event, $prefs);

        // Build facilities filter
        $places = $this->buildFacilitiesFilter($event, $prefs);

        $query = "SELECT DISTINCT cal.*, pat.*,
                         fac.name AS facility_name,
                         u.fname AS provider_fname, u.lname AS provider_lname
                  FROM openemr_postcalendar_events AS cal
                  LEFT JOIN patient_data AS pat ON cal.pc_pid = pat.pid
                  LEFT JOIN facility AS fac ON cal.pc_facility = fac.id
                  LEFT JOIN users AS u ON cal.pc_aid = u.id
                  WHERE cal.pc_eventDate < CURDATE() - INTERVAL " . add_escape_custom($timing) . " DAY
                        AND cal.pc_eventDate > CURDATE() - INTERVAL 365 DAY
                        " . $appt_status . "
                        " . $providers . "
                        " . $places . "
                        AND pat.pid > ''
                        AND (
                          (pat.hipaa_allowsms='YES' AND pat.phone_cell > '') OR
                          (pat.hipaa_allowemail='YES' AND pat.email > '') OR
                          (pat.hipaa_voice='YES' AND (pat.phone_home > '' OR pat.phone_cell > ''))
                        )
                        AND NOT EXISTS (
                            SELECT 1 FROM medex_outgoing
                            WHERE msg_pc_eid = cal.pc_eid
                            AND campaign_uid = '" . add_escape_custom($event['campaign_uid']) . "'
                        )
                  ORDER BY cal.pc_eventDate DESC";

        $appointments = QueryUtils::fetchRecords($query, $escapedArr);

        foreach ($appointments as $appt) {
            // Create survey message for this completed appointment
            $message = [
                'pc_eid' => $appt['pc_eid'],
                'patient_id' => $appt['pid'],
                'patient_name' => $appt['fname'] . ' ' . $appt['lname'],
                'appointment_date' => $appt['pc_eventDate'],
                'appointment_time' => $appt['pc_startTime'],
                'facility' => $appt['facility_name'],
                'provider' => ($appt['provider_fname'] ?? '') . ' ' . ($appt['provider_lname'] ?? ''),
                'event_type' => $event['M_type'],
                'event_id' => $event['id'],
                'campaign_uid' => $event['campaign_uid'],
                'modality' => $this->determineModality($appt, $event),
                'phone' => $this->getPatientPhone($appt),
                'email' => $appt['email'] ?? '',
                'language' => $appt['language'] ?? 'English'
            ];

            // Insert into medex_outgoing as "To Send"
            $this->insertOutgoingMessage($message);

            $messages[] = $message;
        }

        return $messages;
    }

    /**
     * Process clinical reminders
     *
     * @param array<string,mixed> $event
     * @param array<string,mixed> $prefs
     * @return array<array<string,mixed>>
     */
    private function processClinicalReminders(array $event, array $prefs): array
    {
        $messages = [];

        // Clinical reminders are stored in patient_reminders table
        $query = "SELECT pr.*, pat.*
                  FROM patient_reminders AS pr
                  LEFT JOIN patient_data AS pat ON pr.pid = pat.pid
                  WHERE pr.active = '1'
                        AND pr.date_sent IS NULL
                        AND pat.pid > ''
                        AND (
                          (pat.hipaa_allowsms='YES' AND pat.phone_cell > '') OR
                          (pat.hipaa_allowemail='YES' AND pat.email > '') OR
                          (pat.hipaa_voice='YES' AND (pat.phone_home > '' OR pat.phone_cell > ''))
                        )
                  ORDER BY pr.due_status, pr.date_created";

        $reminders = QueryUtils::fetchRecords($query, []);

        foreach ($reminders as $reminder) {
            // Create message for this clinical reminder
            $message = [
                'pc_eid' => 'reminder_' . $reminder['id'],
                'patient_id' => $reminder['pid'],
                'patient_name' => $reminder['fname'] . ' ' . $reminder['lname'],
                'reminder_type' => $reminder['category'] ?? 'Clinical Reminder',
                'reminder_due_status' => $reminder['due_status'] ?? '',
                'event_type' => $event['M_type'],
                'event_id' => $event['id'],
                'campaign_uid' => $event['campaign_uid'],
                'modality' => $this->determineModality($reminder, $event),
                'phone' => $this->getPatientPhone($reminder),
                'email' => $reminder['email'] ?? '',
                'language' => $reminder['language'] ?? 'English'
            ];

            // Insert into medex_outgoing as "To Send"
            $this->insertOutgoingMessage($message);

            // Mark reminder as sent
            sqlStatement(
                "UPDATE patient_reminders SET date_sent = NOW() WHERE id = ?",
                [$reminder['id']]
            );

            $messages[] = $message;
        }

        return $messages;
    }

    /**
     * Process GoGreen (checked-out patients)
     *
     * @param array<string,mixed> $event
     * @param array<string,mixed> $prefs
     * @param string $targetLang
     * @param array<string> $escapedArr
     * @return array<array<string,mixed>>
     */
    private function processGoGreen(array $event, array $prefs, string $targetLang, array $escapedArr): array
    {
        $messages = [];

        // Build appointment status filter (checked out/completed statuses)
        $appt_status = $this->buildApptStatusFilter($event);

        // Build providers filter
        $providers = $this->buildProvidersFilter($event, $prefs);

        // Build facilities filter
        $places = $this->buildFacilitiesFilter($event, $prefs);

        // Build visit types filter
        $visit_types = $this->buildVisitTypesFilter($event);

        // Build date range - GoGreen messages sent shortly after checkout
        $timing = $event['E_fire_time'] ?? '1';
        $timing2 = $event['E_fire_time2'] ?? '60';

        $target_dates = "cal.pc_eventDate >= (CURDATE() - INTERVAL " . add_escape_custom($timing2) . " DAY)
                        AND cal.pc_eventDate <= (CURDATE() - INTERVAL " . add_escape_custom($timing) . " DAY)";

        // Avoid duplicate messages
        $no_dupes = "AND NOT EXISTS (
                        SELECT 1 FROM medex_outgoing
                        WHERE msg_pc_eid = cal.pc_eid
                        AND campaign_uid = '" . add_escape_custom($event['campaign_uid']) . "'
                        AND msg_date > (CURDATE() - INTERVAL 90 DAY)
                    )";

        $query = "SELECT DISTINCT cal.*, pat.*,
                         fac.name AS facility_name,
                         u.fname AS provider_fname, u.lname AS provider_lname
                  FROM openemr_postcalendar_events AS cal
                  LEFT JOIN patient_data AS pat ON cal.pc_pid = pat.pid
                  LEFT JOIN facility AS fac ON cal.pc_facility = fac.id
                  LEFT JOIN users AS u ON cal.pc_aid = u.id
                  WHERE " . $target_dates . "
                        " . $targetLang . "
                        " . $appt_status . "
                        " . $providers . "
                        " . $places . "
                        " . $visit_types . "
                        " . $no_dupes . "
                        AND pat.pid > ''
                        AND (
                          (pat.hipaa_allowsms='YES' AND pat.phone_cell > '') OR
                          (pat.hipaa_allowemail='YES' AND pat.email > '') OR
                          (pat.hipaa_voice='YES' AND (pat.phone_home > '' OR pat.phone_cell > ''))
                        )
                  ORDER BY cal.pc_eventDate, cal.pc_startTime";

        $appointments = QueryUtils::fetchRecords($query, $escapedArr);

        foreach ($appointments as $appt) {
            // Create GoGreen message for this checked-out patient
            $message = [
                'pc_eid' => $appt['pc_eid'],
                'patient_id' => $appt['pid'],
                'patient_name' => $appt['fname'] . ' ' . $appt['lname'],
                'appointment_date' => $appt['pc_eventDate'],
                'appointment_time' => $appt['pc_startTime'],
                'facility' => $appt['facility_name'],
                'provider' => ($appt['provider_fname'] ?? '') . ' ' . ($appt['provider_lname'] ?? ''),
                'event_type' => $event['M_type'],
                'event_id' => $event['id'],
                'campaign_uid' => $event['campaign_uid'],
                'modality' => $this->determineModality($appt, $event),
                'phone' => $this->getPatientPhone($appt),
                'email' => $appt['email'] ?? '',
                'language' => $appt['language'] ?? 'English'
            ];

            // Insert into medex_outgoing as "To Send"
            $this->insertOutgoingMessage($message);

            $messages[] = $message;
        }

        return $messages;
    }

    /**
     * Determine best communication modality for patient
     *
     * @param array<string,mixed> $appt
     * @param array<string,mixed> $event
     * @return string
     */
    private function determineModality(array $appt, array $event): string
    {
        // Priority: SMS > Email > AVM (voice)
        if ($event['M_type'] == 'SMS' && $appt['hipaa_allowsms'] == 'YES' && !empty($appt['phone_cell'])) {
            return 'SMS';
        } elseif ($event['M_type'] == 'EMAIL' && $appt['hipaa_allowemail'] == 'YES' && !empty($appt['email'])) {
            return 'EMAIL';
        } elseif ($event['M_type'] == 'AVM' && $appt['hipaa_voice'] == 'YES' && (!empty($appt['phone_home']) || !empty($appt['phone_cell']))) {
            return 'AVM';
        }

        return 'NONE';
    }

    /**
     * Get patient's best phone number
     *
     * @param array<string,mixed> $appt
     * @return string
     */
    private function getPatientPhone(array $appt): string
    {
        if (!empty($appt['phone_cell'])) {
            return preg_replace('/[^0-9]/', '', $appt['phone_cell']);
        } elseif (!empty($appt['phone_home'])) {
            return preg_replace('/[^0-9]/', '', $appt['phone_home']);
        }
        return '';
    }

    /**
     * Insert message into medex_outgoing table
     *
     * @param array<string,mixed> $message
     * @return void
     */
    private function insertOutgoingMessage(array $message): void
    {
        $sql = "INSERT INTO medex_outgoing
                (msg_pc_eid, msg_pid, campaign_uid, msg_type, msg_reply, msg_date, medex_uid)
                VALUES (?, ?, ?, ?, 'To Send', UTC_TIMESTAMP(), ?)";

        QueryUtils::sqlStatementThrowException($sql, [
            $message['pc_eid'],
            $message['patient_id'],
            $message['campaign_uid'],
            $message['modality'],
            $message['event_id']
        ]);
    }

    /**
     * Send generated messages to MedEx server
     *
     * @param string $token
     * @param array<array<string,mixed>> $messages
     * @return array<string,mixed>
     */
    private function sendMessagesToMedEx(string $token, array $messages): array
    {
        try {
            $response = $this->medexApi->makeRequest(
                'index.php?route=api/custom/processMessages&token=' . urlencode($token),
                ['messages' => $messages],
                'POST'
            );

            return $response;
        } catch (\Exception $e) {
            error_log("MedEx Send Messages Error: " . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Get message icons from database
     *
     * @return array<string,array<string,string>>
     */
    private function getMessageIcons(): array
    {
        $icons = [];
        $iconRecords = QueryUtils::fetchRecords("SELECT * FROM medex_icons");

        foreach ($iconRecords as $icon) {
            $icons[$icon['msg_type']][$icon['msg_status']] = $icon['i_html'];
        }

        return $icons;
    }
}
